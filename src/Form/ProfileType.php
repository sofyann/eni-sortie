<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom indiqué doit contenir au minimum 2 caractères.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ],
                'label' => ' Nom',
                'attr' => ['class' => 'nameClass']
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom indiqué doit contenir au minimum 2 caractères.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ],
                'label' => ' Prénom',
                'attr' => ['class' => 'firstnameClass']
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse mail saisie est invalide.'
                    ])
                ],
                'label' => ' Mail',
                'attr' => ['class' => 'emailClass']
            ])
            ->add('phone', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 10,
                        'minMessage' => 'Le numéro de téléphone saisie est invalide.',
                        'maxMessage' => 'Le nom indiqué doit contenir au maximum 100 caractères.'
                    ])
                ],
                'label' => ' Telephone',
                'attr' => ['class' => 'phoneClass']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
