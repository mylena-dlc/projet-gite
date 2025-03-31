<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TestTwilioController
{
    #[Route('/test-twilio', name: 'test_twilio')]
    public function testSms(TexterInterface $texter, LoggerInterface $logger): Response
    {
        $phone = '+33636296222'; 
        $message = 'Test Twilio';

        try {
            $sms = new SmsMessage($phone, $message);
            $texter->send($sms);
            $logger->info("SMS envoyé avec succès !");
            return new Response("SMS envoyé avec succès !");
        } catch (\Exception $e) {
            $logger->error(" Erreur d'envoi de SMS : " . $e->getMessage());
            return new Response("Une erreur est survenue lors de l'envoi du SMS.");
        }
    }
}
