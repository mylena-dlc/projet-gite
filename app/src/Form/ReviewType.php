<?php

namespace App\Form;

use App\Entity\Review;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReviewType extends HoneyPotType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('rating', HiddenType::class, [
                'attr' => [
                    'class' => 'rating',
                ],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'La note doit être comprise entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                "required" => true,
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
