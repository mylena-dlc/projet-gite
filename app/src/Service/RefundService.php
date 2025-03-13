<?php
namespace App\Service;

use Stripe\Refund;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class RefundService
{

    public function processStripeRefund(string $paymentIntentId, float $refundAmount): void
    {
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
        Stripe::setApiKey($stripeSecretKey);

        try {
            // Vérifier et effectuer le remboursement
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if (!$paymentIntent) {
                throw new \Exception('Le PaymentIntent est introuvable.');
            }

            // Vérifier si un remboursement a déjà été effectué
            if (!empty($paymentIntent->charges->data[0]->refunds->data)) {
                throw new \Exception('Un remboursement a déjà été effectué pour ce paiement.');
            }

            // Créer le remboursement
            \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => round($refundAmount * 100), // Montant en centimes
            ]);

        } catch (\Exception $e) {
            throw new \Exception('Erreur Stripe : ' . $e->getMessage());
        }
    }
}
