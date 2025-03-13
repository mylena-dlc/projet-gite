<?php

namespace App\Form;

use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends HoneyPotType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
        ->add('email', EmailType::class, [
            'label' => 'Votre adresse mail',
            'required' => true,
            'attr' => [
                'placeholder' => 'Sélectionnez votre adresse mail',
                'class' => 'border p-3 rounded w-full focus:outline-none focus:ring bg-white'
            ]
        ])
        ->add('subject', TextType::class, [
            'label' => 'Sujet',
            'required' => true,
            'attr' => [
                'placeholder' => 'Objet de votre demande',
                'class' => 'border p-3 rounded w-full focus:outline-none focus:ring bg-white'
            ]
        ])
        ->add('message', TextareaType::class, [
            'label' => 'Votre message',
            'required' => true,
            'attr' => [
                'placeholder' => 'Quel est votre demande ?',
                'class' => 'border p-3 rounded w-full h-32 focus:outline-none focus:ring'
            ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
