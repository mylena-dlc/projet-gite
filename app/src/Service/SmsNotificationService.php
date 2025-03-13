<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Notifier\Message\SmsMessage;

class SmsNotificationService
{

    private TexterInterface $texter;
    private LoggerInterface $logger;

    public function __construct(TexterInterface $texter, LoggerInterface $logger)
    {
        $this->texter = $texter;
        $this->logger = $logger;
    }

    public function sendSms(string $to, string $message): void
    {
        $this->logger->info("Tentative d'envoi de SMS à $to avec le message : $message");

        try {
            $sms = new SmsMessage($to, $message);
            $this->texter->send($sms);
    
            $this->logger->info("✅ SMS envoyé avec succès à $to !");
        } catch (\Exception $e) {
            $this->logger->error("❌ Erreur d'envoi de SMS : " . $e->getMessage());
        }
}

}