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

class ActiviteType extends AbstractType
{
    const GOUVERNORATS = [
        'Ariana'=>'Ariana','Béja'=>'Béja','Ben Arous'=>'Ben Arous','Bizerte'=>'Bizerte',
        'Gabès'=>'Gabès','Gafsa'=>'Gafsa','Jendouba'=>'Jendouba','Kairouan'=>'Kairouan',
        'Kasserine'=>'Kasserine','Kébili'=>'Kébili','Le Kef'=>'Le Kef','Mahdia'=>'Mahdia',
        'La Manouba'=>'La Manouba','Médenine'=>'Médenine','Monastir'=>'Monastir',
        'Nabeul'=>'Nabeul','Sfax'=>'Sfax','Sidi Bouzid'=>'Sidi Bouzid','Siliana'=>'Siliana',
        'Sousse'=>'Sousse','Tataouine'=>'Tataouine','Tozeur'=>'Tozeur','Tunis'=>'Tunis','Zaghouan'=>'Zaghouan',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'activité',
                'attr'  => ['placeholder' => 'Ex: Randonnée au Djebel Zaghouan'],
            ])
            ->add('type', ChoiceType::class, [
                'label'       => 'Type d\'activité',
                'placeholder' => '-- Choisir un type --',
                'choices'     => ['Sport'=>'Sport','Culture'=>'Culture','Gastronomie'=>'Gastronomie','Aventure'=>'Aventure','Bien-être'=>'Bien-être','Autre'=>'Autre'],
            ])
            ->add('genreCible', ChoiceType::class, [
                'label'       => 'Genre cible',
                'placeholder' => '-- Choisir --',
                'choices'     => ['Mixte'=>'MIXTE','Masculin'=>'MASCULIN','Féminin'=>'FEMININ'],
            ])
            ->add('lieu', ChoiceType::class, [
                'label'       => 'Gouvernorat',
                'placeholder' => '-- Choisir un gouvernorat --',
                'choices'     => self::GOUVERNORATS,
            ])
            ->add('avecDate', CheckboxType::class, [
                'label'    => 'Fixer une date pour cette activité',
                'required' => false,
                'mapped'   => false,
            ])
            ->add('dateActivite', DateTimeType::class, [
                'label'    => 'Date de l\'activité',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => ['rows' => 4, 'placeholder' => 'Décrivez l\'activité...'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'scale' => 2,
                'attr'  => ['placeholder' => '0.00', 'step' => '0.01', 'min' => '0.01'],
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr'  => ['placeholder' => 'Ex: 20', 'min' => 1],
            ])
            ->add('imageFile', FileType::class, [
                'label'       => 'Image de l\'activité',
                'required'    => false,
                'mapped'      => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '10M',
                        'mimeTypes'        => ['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/svg+xml','image/tiff'],
                        'mimeTypesMessage' => 'Format accepté : JPG, PNG, WEBP, GIF, BMP, SVG, TIFF.',
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
