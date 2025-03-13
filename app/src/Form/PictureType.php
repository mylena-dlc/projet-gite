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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PictureType extends AbstractType
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
            // ->add('is_cover', CheckboxType::class, [
            //     'label' => 'Ajouter en image de couverture catégorie',
            //     'required' => false,
            //     'row_attr' => [
            //         'class' => 'flex items-center gap-2'
            //     ]
            // ])
            // ->add('gite', EntityType::class, [
            //     'class' => Gite::class,
            //     'choice_label' => 'id',
            // ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name', // Remplacez 'name' par la propriété que vous souhaitez afficher
                'multiple' => false, // Autorise la sélection d'une seule catégorie
                'expanded' => true, // Affiche les catégories comme des cases à cocher            
                'required' => false, // Rend le champ non obligatoire
                'placeholder' => 'Sans catégorie', // Définit la valeur du placeholder
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
