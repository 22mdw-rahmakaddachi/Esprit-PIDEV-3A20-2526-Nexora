<?php

namespace App\Form;

use App\Entity\CodePromo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodePromoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label'      => 'Code promo',
                'attr'       => ['placeholder' => 'ex: SUMMER20', 'style' => 'text-transform:uppercase'],
                'empty_data' => '',
            ])
            ->add('typeReduction', ChoiceType::class, [
                'label'      => 'Type de réduction',
                'choices'    => ['Pourcentage (%)' => 'pourcentage', 'Montant fixe (TND)' => 'montant_fixe'],
                'empty_data' => 'pourcentage',
            ])
            ->add('valeurReduction', NumberType::class, [
                'label' => 'Valeur de la réduction',
                'scale' => 2,
                'attr'  => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('montantMinimum', NumberType::class, [
                'label'    => 'Montant minimum de commande (TND)',
                'required' => false,
                'scale'    => 2,
            ])
            ->add('dateDebut', DateType::class, [
                'label'  => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('dateFin', DateType::class, [
                'label'  => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('limiteUtilisation', IntegerType::class, [
                'label'    => 'Limite d\'utilisation',
                'required' => false,
                'attr'     => ['min' => 1, 'placeholder' => 'Illimité'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 2],
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CodePromo::class]);
    }
}
