<?php

namespace App\Form;

use App\Entity\Gite;
use App\Entity\Period;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class PeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('startDate', DateType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text', // Utiliser un widget de saisie unique
            'format' => 'yyyy-MM-dd', // Format personnalisé
            'required' => true,
            'attr' => [
                'placeholder' => 'Sélectionnez le début de la période', // Ajouter un attribut de placeholder
            ],
        ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text', // Utiliser un widget de saisie unique
                'format' => 'yyyy-MM-dd', // Format personnalisé
                'required' => true,
                'attr' => [
                    'placeholder' => 'Sélectionnez la fin de la période', // Ajouter un attribut de placeholder
                ],
    
            ])
            ->add('supplement', MoneyType::class, [
                'label' => 'Supplément en €',
                'required' => true,
                'currency' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Period::class,
        ]);
    }
}
