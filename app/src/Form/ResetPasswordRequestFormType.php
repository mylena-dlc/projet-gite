<?php

namespace App\Form;

use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ResetPasswordRequestFormType extends HoneyPotType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
        ->add('email', EmailType::class, [
            'label' => 'Entrez votre adresse mail',
            'attr' => [
                'placeholder' => 'exemple@email.fr',
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
