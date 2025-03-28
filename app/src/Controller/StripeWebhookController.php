<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Entity\Gite;
use App\Entity\User;
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
    public function handleStripeWebhook(Request $request): Response
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
            $this->logger->error("Erreur : Payload invalide");
            return new Response('Erreur : Payload invalide.', 400);
        } catch (SignatureVerificationException $e) {
            $this->logger->error("Erreur : Signature Stripe invalide");
            return new Response('Erreur : Signature invalide.', 400);
        }

        // Gestion des événements Stripe 
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->logger->info("Paiement confirmé pour session ID : " . $event->data->object->id);
                $this->handleSuccessfulPayment($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->logger->warning("Échec de paiement pour : " . $event->data->object->id);
                $this->handlePaymentFailed($event->data->object);
                break;

            default:
                $this->logger->warning("Événement Stripe non traité : " . $event->type);
                return new Response('Événement non traité.', 400);
        }

        return new Response('Webhook traité avec succès', 200);
    }


    /**
    * Fonction pour créé la réservation en BDD
    */
    private function handleSuccessfulPayment($session)
    {

        $this->logger->info('Stripe session reçue : ' . $session->id);
        $this->logger->info('PaymentIntent : ' . $session->payment_intent);

        $metadata = $session->metadata;
        $reservation = new Reservation();
        $reservation->setArrivalDate(\DateTime::createFromFormat('d/m/Y', $metadata->start_date));
        $reservation->setDepartureDate(\DateTime::createFromFormat('d/m/Y', $metadata->end_date));
        $reservation->setNumberAdult($metadata->number_adult);
        $reservation->setNumberKid($metadata->number_kid);
        $reservation->setTotalNight($metadata->total_night);
        $reservation->setPriceNight($metadata->night_price);
        $reservation->setCleaningCharge($metadata->cleaning_charge);
        $reservation->setSupplement($metadata->supplement);
        $reservation->setTva($metadata->tva);
        $reservation->setTourismTax($metadata->tax);
        $reservation->setTotalPrice($metadata->total_price);
        $reservation->setLastName($metadata->last_name);
        $reservation->setFirstName($metadata->first_name);
        $reservation->setAddress($metadata->address);
        $reservation->setCp($metadata->cp);
        $reservation->setCity($metadata->city);
        $reservation->setCountry($metadata->country);
        $reservation->setPhone($metadata->phone);
        $reservation->setEmail($metadata->email);
        $reservation->setIsMajor($metadata->is_major === 1); 
        $reservation->setMessage($metadata->message);
        $reservation->setStripePaymentId($session->payment_intent);
    
        // Référence unique
        $uuid = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $reference = 'RES-' . date('Y') . '-' . $uuid;
        $reservation->setReference($reference);

        // Relations
        $user = $this->entityManager->getRepository(User::class)->find($metadata->user_id);
        $gite = $this->entityManager->getRepository(Gite::class)->find($metadata->gite_id);
        $reservation->setUser($user);
        $reservation->setGite($gite);

        try {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            $this->logger->info("Réservation confirmée et enregistrée (Webhook Stripe)");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la sauvegarde : " . $e->getMessage());
        }
    }
    

    /**
     * Gestion du paiement échoué (payment_intent.payment_failed)
     */
    private function handlePaymentFailed($paymentIntent)
    {
        $this->logger->warning("Échec du paiement pour PaymentIntent ID : " . $paymentIntent->id);

        // Aucune action en base, juste log l'info
        $this->logger->info("Aucune réservation à supprimer. L'utilisateur a probablement annulé avant le paiement.");
    }

}
