<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ActiviteType extends AbstractType
{
    const GOUVERNORATS = [
        'Ariana'         => 'Ariana',
        'Béja'           => 'Béja',
        'Ben Arous'      => 'Ben Arous',
        'Bizerte'        => 'Bizerte',
        'Gabès'          => 'Gabès',
        'Gafsa'          => 'Gafsa',
        'Jendouba'       => 'Jendouba',
        'Kairouan'       => 'Kairouan',
        'Kasserine'      => 'Kasserine',
        'Kébili'         => 'Kébili',
        'Le Kef'         => 'Le Kef',
        'Mahdia'         => 'Mahdia',
        'La Manouba'     => 'La Manouba',
        'Médenine'       => 'Médenine',
        'Monastir'       => 'Monastir',
        'Nabeul'         => 'Nabeul',
        'Sfax'           => 'Sfax',
        'Sidi Bouzid'    => 'Sidi Bouzid',
        'Siliana'        => 'Siliana',
        'Sousse'         => 'Sousse',
        'Tataouine'      => 'Tataouine',
        'Tozeur'         => 'Tozeur',
        'Tunis'          => 'Tunis',
        'Zaghouan'       => 'Zaghouan',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'       => 'Nom de l\'activité',
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire')],
                'attr'        => ['placeholder' => 'Ex: Randonnée au Djebel Zaghouan'],
            ])
            ->add('type', ChoiceType::class, [
                'label'   => 'Type d\'activité',
                'choices' => [
                    'Sport'       => 'Sport',
                    'Culture'     => 'Culture',
                    'Gastronomie' => 'Gastronomie',
                    'Aventure'    => 'Aventure',
                    'Bien-être'   => 'Bien-être',
                    'Autre'       => 'Autre',
                ],
            ])
            ->add('genreCible', ChoiceType::class, [
                'label'   => 'Genre cible',
                'choices' => [
                    'Mixte'    => 'MIXTE',
                    'Masculin' => 'MASCULIN',
                    'Féminin'  => 'FEMININ',
                ],
            ])
            ->add('lieu', ChoiceType::class, [
                'label'       => 'Gouvernorat',
                'choices'     => self::GOUVERNORATS,
                'placeholder' => '-- Choisir un gouvernorat --',
                'constraints' => [new NotBlank(message: 'Le lieu est obligatoire')],
            ])
            ->add('avecDate', CheckboxType::class, [
                'label'    => 'Fixer une date pour cette activité',
                'required' => false,
                'mapped'   => false, // géré manuellement dans le controller
            ])
            ->add('dateActivite', DateTimeType::class, [
                'label'    => 'Date de l\'activité',
                'required' => false,
                'widget'   => 'single_text',
                'attr'     => ['min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i')],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => 'Décrivez l\'activité...'],
            ])
            ->add('prix', TextType::class, [
                'label'       => 'Prix (TND)',
                'constraints' => [new NotBlank(message: 'Le prix est obligatoire')],
                'attr'        => ['placeholder' => '0.00', 'pattern' => '[0-9]+([.,][0-9]{1,2})?'],
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label'       => 'Nombre de places',
                'constraints' => [new Positive(message: 'Le nombre de places doit être positif')],
                'attr'        => ['placeholder' => 'Ex: 20', 'min' => 1],
            ])
            ->add('imageUrl', TextType::class, [
                'label'    => 'URL de l\'image (optionnel)',
                'required' => false,
                'mapped'   => false,
                'attr'     => ['placeholder' => 'https://exemple.com/image.jpg'],
            ])
            ->add('imageFile', FileType::class, [
                'label'       => 'Ou uploader une image depuis votre PC',
                'required'    => false,
                'mapped'      => false,
                'constraints' => [
                    new File([
                        'maxSize'   => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Format accepté : JPG, PNG, WEBP, GIF',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Activite::class]);
    }
}
