<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom sortie',
            ])
            ->add('dateBeginning', DateTimeType::class, [
                'label' => 'Date de sortie',
            ])
            ->add('duration', null, [
                'label' => 'Durée de la sortie (en minute)',
                'attr' => array('min' => 0),
            ])
            ->add('registrationDeadline', null, [
                'label' => 'Delais d\'inscription',
            ])
            ->add('registrationMax', null, [
                'label' => 'Nombre de participant maximum',
                'attr' => array('min' => 0),
            ])
            ->add('info', null, [
                'label' => 'Description',
            ])
            ->add('location',LocationType::class, [
                'label' => 'Adresse',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
