<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('prenom', TextType::class, ['empty_data' => ''])
            ->add('nom', TextType::class, ['empty_data' => ''])
            ->add('email', EmailType::class, ['empty_data' => ''])
            ->add('num', IntegerType::class)
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Admin'       => 'ROLE_ADMIN',
                    'Partenaire'  => 'ROLE_PARTENAIRE',
                    'Participant' => 'ROLE_PARTICIPANT',
                ],
                'placeholder'  => 'Choisir un rôle',
                'empty_data'   => '',
                'constraints'  => [
                    new NotBlank(message: 'Le rôle est obligatoire.'),
                ],
            ])
            ->add('mdp', PasswordType::class, [
                'required'    => !$isEdit,
                'mapped'      => false,
                'constraints' => $isEdit ? [] : [
                    new NotBlank(message: 'Le mot de passe est obligatoire.'),
                    new Length(min: 6, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'is_edit'    => false,
        ]);
    }
}
