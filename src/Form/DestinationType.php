<?php

namespace App\Form;

use App\Entity\Destination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'label'       => "Nom de l'excursion",
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire'])],
                'attr'        => ['placeholder' => 'Ex: Paris, Marrakech...'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => "Décrivez l'excursion..."],
            ])
            ->add('localisation', TextType::class, [
                'label'       => 'Localisation',
                'constraints' => [new NotBlank(['message' => 'La localisation est obligatoire'])],
                'attr'        => [
                    'placeholder'  => 'Ville, Pays...',
                    'autocomplete' => 'off',
                    'id'           => 'localisation-field',
                ],
            ])
            ->add('capaciteMax', IntegerType::class, [
                'label' => 'Nombre maximal de participants',
                'attr'  => ['min' => 1, 'class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'La capacité maximale est obligatoire'])],
            ])
            ->add('dateLancement', DateTimeType::class, [
                'label'    => 'Date de lancement / départ',
                'widget'   => 'single_text',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('currency', TextType::class, [
                'label'    => 'Monnaie locale',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: Euro (€), Dollar ($)...'],
            ])
            ->add('plugType', TextType::class, [
                'label'    => 'Type de prise',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: Type E / F / C...'],
            ])
            ->add('survivalPhrases', TextareaType::class, [
                'label'    => 'Phrases de survie',
                'required' => false,
                'attr'     => ['rows' => 2, 'placeholder' => 'Ex: Bonjour / Merci / S\'il vous plaît'],
            ])
            ->add('panoramaUrl', TextType::class, [
                'label'    => 'Lien Panorama 360° / VR',
                'required' => false,
                'help'     => 'Format recommandé : Code "Intégrer une carte" (iframe) ou URL complète de la barre d\'adresse.',
                'attr'     => ['placeholder' => 'Code <iframe... ou URL complète maps.google.com/...'],
            ]);


        $builder
            // ── Champ multi-fichiers (non mappé, géré manuellement dans le contrôleur) ──
            ->add('imageFiles', FileType::class, [
                'label'       => "Images de l'excursion",
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
