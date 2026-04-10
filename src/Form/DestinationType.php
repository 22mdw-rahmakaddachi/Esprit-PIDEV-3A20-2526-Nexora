<?php

namespace App\Form;

use App\Entity\Destination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DestinationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'       => 'Nom de la destination',
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire'])],
                'attr'        => ['placeholder' => 'Ex: Paris, Marrakech...'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => 'Décrivez la destination...'],
            ])
            ->add('localisation', TextType::class, [
                'label'       => 'Localisation',
                'constraints' => [new NotBlank(['message' => 'La localisation est obligatoire'])],
                'attr'        => [
                    'placeholder'  => 'Ville, Pays...',
                    'autocomplete' => 'off',
                    'id'           => 'localisation-field',
                ],
            ]);

        // N'afficher le champ statut que lors de l'édition (si la destination a déjà un ID)
        if ($builder->getData() && $builder->getData()->getId() !== null) {
            $builder->add('statut', ChoiceType::class, [
                'label'       => 'Statut',
                'choices'     => [
                    'Disponible' => 'Disponible',
                    'Complet'    => 'Complet',
                ],
                'placeholder' => 'Sélectionner le statut',
                'constraints' => [new NotBlank(['message' => 'Le statut est obligatoire'])],
            ]);
        }

        $builder
            // ── Champ multi-fichiers (non mappé, géré manuellement dans le contrôleur) ──
            ->add('imageFiles', FileType::class, [
                'label'       => 'Images de la destination',
                'mapped'      => false,
                'required'    => false,
                'multiple'    => true,
                'attr'        => [
                    'multiple' => 'multiple',
                    'accept'   => 'image/jpeg,image/png,image/webp',
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize'            => '30M',
                                'mimeTypes'          => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage'   => 'Veuillez uploader des images valides (JPG, PNG, WEBP)',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Destination::class]);
    }
}
