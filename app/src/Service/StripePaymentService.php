<?php

namespace App\Service;

use Stripe\Event;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Entity\Gite;
use App\Entity\User;
use App\Entity\Extra;
use App\Entity\Reservation;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Symfony\Component\Uid\Uuid;
use App\Entity\ReservationExtra;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;

class StripePaymentService
{
    private string $stripeSecretKey;
    private string $stripeWebhookSecret;
    private EntityManagerInterface $entityManager;
    private ReservationRepository $reservationRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        LoggerInterface $logger
    ) {
        $this->stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
        $this->stripeWebhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
        $this->logger = $logger;

        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     *  Création d'une session de paiement Stripe
    */
    public function createPaymentSession(array $reservationDetails): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $uniqueId = Uuid::v4();   // Génère un ID unique

        $_SESSION['reservation_ok_' . $uniqueId] = $reservationDetails; 

        $session = Session::create([
            'payment_method_types' => ['card', 'paypal'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Réservation - ' . $reservationDetails['giteName'],
                    ],
                    'unit_amount' => round($reservationDetails['totalPrice'] * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            // 'success_url' => $_ENV['APP_URL'] . '/reservation/confirmation?session_id={CHECKOUT_SESSION_ID}',
            'success_url' => $_ENV['APP_URL'] . '/reservation/confirmation-pending?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => $_ENV['APP_URL'] . '/reservation/error',        
            'metadata' => [
                'user_id' => $reservationDetails['userId'],
                'gite_id' => $reservationDetails['giteId'],
                'start_date' => $reservationDetails['startDate'],
                'end_date' => $reservationDetails['endDate'],
                'number_adult' => $reservationDetails['numberAdult'],
                'number_kid' => $reservationDetails['numberKid'],
                'total_night' => $reservationDetails['totalNight'],
                'night_price' => $reservationDetails['nightPrice'],
                'cleaning_charge' => $reservationDetails['cleaningCharge'],
                'supplement' => $reservationDetails['supplement'],
                'tva' => $reservationDetails['tva'],
                'tax' => $reservationDetails['tax'],
                'total_price' => $reservationDetails['totalPrice'],
                'first_name' => $reservationDetails['firstName'],
                'last_name' => $reservationDetails['lastName'],
                'email' => $reservationDetails['email'],
                'phone' => $reservationDetails['phone'],
                'address' => $reservationDetails['address'],
                'city' => $reservationDetails['city'],
                'cp' => $reservationDetails['cp'],
                'country' => $reservationDetails['country'],
                'is_major' => $reservationDetails['isMajor'],
                'message' => $reservationDetails['message'] ?? '',

            ]
        ]);

        return $session->url;
    }
}
