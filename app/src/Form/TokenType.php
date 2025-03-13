<?php

namespace App\Form;

use DateTime;
use App\Entity\Gite;
use App\Entity\Token;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class TokenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code promo',
            ])
            ->add('discount', NumberType::class, [
                'label' => 'Remise (en %)',
            ])
            ->add('expiration_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'expiration',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Token::class,
        ]);
    }
}
