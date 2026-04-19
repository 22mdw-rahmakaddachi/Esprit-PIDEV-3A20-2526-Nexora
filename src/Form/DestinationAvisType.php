<?php

namespace App\Form;

use App\Entity\DestinationAvis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DestinationAvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', HiddenType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une note']),
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Votre avis',
                'attr' => [
                    'placeholder' => 'Racontez-nous votre expérience...',
                    'rows' => 4,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le commentaire est obligatoire']),
                ],
            ])
            ->add('imageFiles', FileType::class, [
                'label' => 'Photos réelles (Social Proof)',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                ],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '20M',
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Veuillez uploader des images valides (JPG, PNG, WEBP)',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DestinationAvis::class,
        ]);
    }
}
