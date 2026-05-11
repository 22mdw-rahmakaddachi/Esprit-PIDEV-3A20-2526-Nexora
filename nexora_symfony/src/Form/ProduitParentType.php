<?php

namespace App\Form;

use App\Entity\ProduitParent;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitParentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'      => 'Nom du produit',
                'required'   => true,
                'empty_data' => '',
                'attr'       => ['placeholder' => 'ex: Sac à dos randonnée 40L'],
            ])
            ->add('marque', TextType::class, [
                'label'      => 'Marque',
                'required'   => true,
                'empty_data' => '',
                'attr'       => ['placeholder' => 'ex: Quechua'],
            ])
            ->add('sousCategorie', EntityType::class, [
                'class'        => SousCategorie::class,
                'choice_label' => 'nom',
                'label'        => 'Catégorie',
                'required'     => false,
                'placeholder'  => '— Choisir —',
            ])
            ->add('descriptionCourte', TextType::class, [
                'label'      => 'Description courte',
                'required'   => true,
                'empty_data' => '',
                'attr'       => ['placeholder' => 'Résumé en une ligne affiché dans la boutique'],
            ])
            ->add('description', TextareaType::class, [
                'label'      => 'Description complète',
                'required'   => true,
                'empty_data' => '',
                'attr'       => ['rows' => 4, 'placeholder' => 'Description détaillée du produit...'],
            ])
            ->add('materiau', TextType::class, [
                'label'      => 'Matériau',
                'required'   => true,
                'empty_data' => '',
                'attr'       => ['placeholder' => 'ex: Nylon 600D imperméable'],
            ])
            ->add('poidsKg', NumberType::class, [
                'label'    => 'Poids (kg)',
                'required' => true,
                'scale'    => 2,
                'attr'     => ['placeholder' => 'ex: 1.50', 'min' => 0, 'step' => '0.01'],
            ])
            ->add('dimensionsCm', TextType::class, [
                'label'      => 'Dimensions (cm)',
                'required'   => false,
                'empty_data' => '',
                'attr'       => ['placeholder' => 'ex: 60x30x20'],
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => ['Actif (visible en boutique)' => 'actif', 'Inactif (masqué)' => 'inactif'],
            ])
            ->add('imagePrincipale', FileType::class, [
                'label'    => 'Image principale',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '5M',
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WebP).',
                        'maxSizeMessage'   => "L'image ne doit pas dépasser 5 Mo.",
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer le produit']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProduitParent::class]);
    }
}
