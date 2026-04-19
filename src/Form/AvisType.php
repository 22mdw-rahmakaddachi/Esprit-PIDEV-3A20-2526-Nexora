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
            ->add('auteur', TextType::class, [
                'label' => 'Nom de l\'auteur',
                'attr'  => [
                    'readonly'    => true,
                    'placeholder' => 'Ex: Jean Dupont',
                    'style'       => 'background:#f9f7ff;cursor:not-allowed;color:var(--primary-dark);font-weight:600',
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'attr'  => ['rows' => 4, 'placeholder' => 'Votre avis sur cette activité...'],
            ])
            ->add('note', ChoiceType::class, [
                'label'   => 'Note',
                'choices' => [
                    '⭐ 1 — Très mauvais' => 1,
                    '⭐⭐ 2 — Mauvais'    => 2,
                    '⭐⭐⭐ 3 — Moyen'    => 3,
                    '⭐⭐⭐⭐ 4 — Bien'   => 4,
                    '⭐⭐⭐⭐⭐ 5 — Excellent' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Avis::class]);
    }
}
