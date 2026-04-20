<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr'  => ['placeholder' => 'Résumé de votre avis'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Commentaire',
                'attr'  => ['rows' => 4, 'placeholder' => 'Votre avis détaillé...'],
            ])
            ->add('rating', ChoiceType::class, [
                'label'   => 'Note',
                'choices' => [
                    '⭐ 1 — Très mauvais'    => 1,
                    '⭐⭐ 2 — Mauvais'        => 2,
                    '⭐⭐⭐ 3 — Moyen'        => 3,
                    '⭐⭐⭐⭐ 4 — Bien'       => 4,
                    '⭐⭐⭐⭐⭐ 5 — Excellent' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Avis::class]);
    }
}
