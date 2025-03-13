<?php

namespace App\Form;

use App\Entity\User;
use App\Form\FormExtension\HoneyPotType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ChangePasswordType extends HoneyPotType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false,
                'row_attr' => ['class' => 'w-full'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe actuel']),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'row_attr' => ['class' => 'w-full'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un nouveau mot de passe']),
                    new Regex([
                        'pattern' => 
                        '~^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()-_+=<>?])(?!.*\s).{12}$~',
                        // au moins 1 majuscule - au moins 1 minuscule - au moins 1 chiffre - 
                        // au moins un caractère special - aucun espace - au moins 12 caractères
                        'match' => true, // la valeur soumise doit correspondre entièrement à la Regex
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule,
                         un chiffre et avoir au moins 12 caractères.',
                    ]),
                ],
            ]);
                }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
