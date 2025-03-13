<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\Reservation;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

   /**
 * Fonction de gestion des évènements Stripe
 */
#[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
public function handleStripeWebhook(Request $request, LoggerInterface $logger): Response
{
    $this->logger->info("Webhook Stripe reçu");

    // Récupération des données Stripe
    $payload = @file_get_contents('php://input');
    $sigHeader = $request->headers->get('stripe-signature');
    $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];
    $event = null;

    try {
        // Vérification de la signature Stripe
        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            $endpointSecret
        );
    } catch (UnexpectedValueException $e) {
        $logger->error("Erreur : Payload invalide");
        return new Response('Erreur : Payload invalide.', 400);
    } catch (SignatureVerificationException $e) {
        $logger->error("Erreur : Signature Stripe invalide");
        return new Response('Erreur : Signature invalide.', 400);
    }

    // Gestion des événements Stripe avec un switch
    switch ($event->type) {
        case 'checkout.session.completed':
            $logger->info("Paiement confirmé pour session ID : " . $event->data->object->id);
            $this->handleSuccessfulPayment($event->data->object);
            break;

        case 'payment_intent.succeeded':
            $logger->info("PaymentIntent réussi : " . $event->data->object->id);
            $this->handlePaymentSucceeded($event->data->object);
            break;

        case 'charge.succeeded':
            $logger->info("Charge réussie pour ID : " . $event->data->object->id);
            $this->handleChargeSucceeded($event->data->object);
            break;

        case 'payment_intent.payment_failed':
            $logger->warning("Échec de paiement pour : " . $event->data->object->id);
            $this->handlePaymentFailed($event->data->object);
            break;

        default:
            $logger->warning("Événement Stripe non traité : " . $event->type);
            return new Response('Événement non traité.', 400);
    }

    return new Response('Webhook traité avec succès', 200);
}



    /**
    * Fonction pour créé la réservation en BDD
    */
    private function handleSuccessfulPayment($session)
    {
    
        $metadata = $session->metadata;
        $tempReservationId = $metadata->temp_reservation_id;
        $phpSessionId = $metadata->php_session_id;
    
        // Restaurer la session de l'utilisateur
        // if (session_status() === PHP_SESSION_NONE) {
        //     session_id($phpSessionId); // 🔥 Utiliser l'ID de session existant
        //     session_start();
        // }

    
        //  Récupérer les détails de réservation stockés en session
        $reservationDetails = $_SESSION['reservation_ok_' . $tempReservationId] ?? null;
        $this->logger->info($reservationDetails);

        // Création de la réservation
            $reservation = new Reservation();
            $reservation->setArrivalDate(\DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']));
            $reservation->setDepartureDate(\DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']));
            
            $reservation->setNumberAdult($reservationDetails['numberAdult']);
            $reservation->setNumberKid($reservationDetails['numberKid']);
            $reservation->setTotalNight($reservationDetails['totalNight']);
            $reservation->setPriceNight($reservationDetails['nightPrice']);
            $reservation->setCleaningCharge($reservationDetails['cleaningCharge']);
            $reservation->setSupplement($reservationDetails['supplement']);
            $reservation->setTva($reservationDetails['tva']);
            $reservation->setTourismTax($reservationDetails['tax']);
            $reservation->setTotalPrice($reservationDetails['totalPrice']);
            $reservation->setLastName($reservationDetails['lastName']);
            $reservation->setFirstName($reservationDetails['firstName']);
            $reservation->setaddress($reservationDetails['address']);
            $reservation->setCp($reservationDetails['cp']);
            $reservation->setCity($reservationDetails['city']);
            $reservation->setCountry($reservationDetails['country']);
            $reservation->setPhone($reservationDetails['phone']);
            $reservation->setEmail($reservationDetails['email']);
            $reservation->setIsMajor($reservationDetails['isMajor']);
            $reservation->setMessage($reservationDetails['message']);
            $reservation->setReference($tempReservationId);

            // Récupération du Gîte et de l'Utilisateur
            $gite = $this->entityManager->getRepository(\App\Entity\Gite::class)->find($metadata->gite_id);
            $user = $this->entityManager->getRepository(\App\Entity\User::class)->find($metadata->user_id);
            $reservation->setGite($gite);
            $reservation->setUser($user);
        
            // Enregistrement en bdd
            try {
                $this->entityManager->persist($reservation);
                $this->entityManager->flush();
                $this->logger->info("Réservation confirmée !");
            } catch (\Exception $e) {
                $this->logger->error("Erreur lors de l'enregistrement : " . $e->getMessage());
                // dump("ERREUR lors de la sauvegarde de la réservation !");
                // dump($e->getMessage());
                // dump($e->getTraceAsString());
                // die; 
                return;
            }

    }

    private function handlePaymentSucceeded($paymentIntent)
{
    $this->logger->info("Paiement réussi pour PaymentIntent ID : " . $paymentIntent->id);

    // Récupération de la réservation via Stripe PaymentIntent
    $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy([
        'stripePaymentId' => $paymentIntent->id
    ]);

    if (!$reservation) {
        $this->logger->error("Aucune réservation trouvée pour PaymentIntent ID : " . $paymentIntent->id);
        return;
    }

    // Confirmer la réservation
    $reservation->setIsConfirm(true);
    $this->entityManager->persist($reservation);
    $this->entityManager->flush();

    $this->logger->info("Réservation confirmée pour : " . $reservation->getReference());
}

/**
 * 🔹 Gestion du paiement échoué (payment_intent.payment_failed)
 */
private function handlePaymentFailed($paymentIntent)
{
    $this->logger->warning("Échec du paiement pour PaymentIntent ID : " . $paymentIntent->id);

    // Récupération de la réservation
    $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy([
        'stripePaymentId' => $paymentIntent->id
    ]);

    if (!$reservation) {
        $this->logger->error("Aucune réservation trouvée pour PaymentIntent ID : " . $paymentIntent->id);
        return;
    }

    // Marquer la réservation comme non confirmée
    $reservation->setIsConfirm(false);
    $this->entityManager->persist($reservation);
    $this->entityManager->flush();

    $this->logger->info("Réservation non confirmée pour : " . $reservation->getReference());
}

}
