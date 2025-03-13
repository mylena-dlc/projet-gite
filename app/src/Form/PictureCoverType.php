<?php

namespace App\Form;

use App\Entity\Gite;
use App\Entity\Picture;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PictureCoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('picture', FileType::class, [
            'label' => 'Image',
            'mapped' => false,
            'required' => true,
            'attr' => [
                'class' => ''
            ],

            'constraints' => [
                new File([
                    'maxSize' => '4M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                ])
            ],
        ])
        ->add('url', HiddenType::class, [ // Champ "url" de type HiddenType
            'mapped' => false, // Ne pas mapper ce champ à l'entité
        ])
        ->add('alt', TextType::class, [
            'label' => 'Description',
            'required' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
