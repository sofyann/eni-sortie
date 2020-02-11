<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse mail saisie est invalide.'
                    ])
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom indiqué doit contenir au minimum 2 caractères.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom indiqué doit contenir au minimum 2 caractères.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 10,
                        'minMessage' => 'Le numéro de téléphone saisie est invalide.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ]
            ])
            ->add('administrator', CheckboxType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name'
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
