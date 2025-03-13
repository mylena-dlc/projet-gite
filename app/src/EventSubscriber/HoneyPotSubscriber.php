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
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $data = $event->getData();
        if (!array_key_exists('numberPhone', $data) || !array_key_exists('numberFax', $data)) {
            header('Location: /');
            exit;
        }

        [
            'numberPhone' => $numberPhone,
            'numberFax' => $numberFax
        ] = $data;

        if ($numberPhone !== "" || $numberFax !== "") {
            $this->honeyPotLogger->info("Une potentielle tentative de robot spammeur ayant l\'adresse IP suivante '
             {$request->getClientIp()}' a eu lieu.
             Le champ number phone contenait '{$numberPhone}' et le champ numberFax contenait '{$numberFax}'.");
             header('Location: /');
             exit;
        }
    }
}