<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReservationType extends HoneyPotType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
                "required" => true,
            ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
                "required" => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                "required" => true,
            ])
            ->add('cp', TextType::class, [
                'label' => 'Code postal',
                "required" => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un code postal.']),
                ]
            ])
            // ->add('city', ChoiceType::class, [
            //     'label' => 'Ville',
            //     "required" => true,
            //     'placeholder' => 'Sélectionnez une ville',
            //     'choices' => array_combine($options['available_cities'], $options['available_cities']), // 🔥 Passe les villes
            //     'attr' => [
            //         'class' => 'js-city-select', 
            //         'id' => 'reservation_city'
            //     ],
            //     'invalid_message' => 'Veuillez sélectionner une ville valide probleme form.',
            // ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                "required" => true,
                // 'attr' => [
                //     'class' => 'js-city-select', 
                //     'id' => 'reservation_city'
                // ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                "required" => true,
                'preferred_choices' => ['FR'], // Met "France" en premier dans la liste
                'data' => 'FR', // Définit "France" comme sélectionnée par défaut
                'attr' => [
                    'class' => 'flex flex-col bg-white1 rounded-xl w-3/4 p-2', 
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                "required" => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le numéro de téléphone est obligatoire.'
                    ]),
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-]{6,20}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide.',
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => false,
                "required" => false,
            ])
            ->add('is_major', CheckboxType::class, [
                'label' => 'Je confirme être majeur(e)',
                "required" => true,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez être majeur pour réserver un séjour.",
                    ]),
                ],
            ]);
             // Ajout du PRE_SUBMIT 
            // $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            //     $data = $event->getData();
            //     if (isset($data['city']) && is_array($data['city'])) {
            //         $data['city'] = reset($data['city']); // Prend la première valeur si c'est un tableau
            //     }
            //     $event->setData($data);
            // });
                }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'available_cities' => []
        ]);
    }
}
