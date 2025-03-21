<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HoneyPotSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $honeyPotLogger;
    private RequestStack $requestStack;

    public function __construct(
        LoggerInterface $honeyPotLogger,
        RequestStack $requestStack
    )
    {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'checkHoneyJar'
        ];
    }

    public function checkHoneyJar(FormEvent $event): void
    {
        // Désactivation en environnement de test
        if ($_ENV['APP_ENV'] === 'test') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $data = $event->getData();
        if (!isset($data['numberPhone']) || !isset($data['numberFax'])) {
            throw new HttpException(400, "Données invalides");
        }

        [
            'numberPhone' => $numberPhone,
            'numberFax' => $numberFax
        ] = $data;

        if ($numberPhone !== "" || $numberFax !== "") {
            $this->honeyPotLogger->info(
                "Tentative de spam détectée depuis {$request->getClientIp()}.
                Le champ numberPhone contenait '{$numberPhone}' 
                et le champ numberFax contenait '{$numberFax}'."
            );

            // Redirection avec une réponse Symfony propre
            $response = new RedirectResponse('/');
            $response->send();
            exit; // Arrêt immédiat du script après la redirection
        }
    }
}