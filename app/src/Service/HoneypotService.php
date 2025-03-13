<?php

namespace App\Service;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
class HoneypotService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function addHoneypotField(FormBuilderInterface $builder, string $fieldName = 'honeypot'): void
    {
        $builder->add($fieldName, HiddenType::class, [
            'mapped' => false,
            'required' => false,
            'attr' => [
                'style' => 'display:none;',
                'autocomplete' => 'off',
            ],
        ]);
    }

    public function isHoneypotTripped(FormInterface $form, string $fieldName = 'honeypot'): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $honeypotValue = $form->get($fieldName)->getData();
        return !empty($honeypotValue);
    }
}