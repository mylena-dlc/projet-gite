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
            'metadata' => [
                'temp_reservation_id' => $uniqueId,
                'php_session_id' => session_id() // 🔹 Stocke l'ID de session

            ],
        
            'success_url' => $_ENV['APP_URL'] . '/reservation/confirm?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $_ENV['APP_URL'] . '/reservation/error',
        ]);

        return $session->url;
    }
}
