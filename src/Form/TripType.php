<?php

namespace App\Form;

use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\Length;

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
                'attr' =>['class' => 'DateDebut dateInscription '],
                'widget' => 'single_text'


            ])
            ->add('duration', null, [
                'label' => 'Durée de la sortie (en minute)',
                'attr' =>[ 'min' => 0, 'class' => 'DureSortie ']
            ])
            ->add('registrationDeadline', DateTimeType::class, [
                'label' => 'Delais d\'inscription',
                'widget' => 'single_text'
            ])
            ->add('registrationMax', null, [
                'label' => 'Nombre de participant maximum',
                'attr' => [ 'min' => 0, 'class' => 'RegistrationMaxDate dateInscription  '],

            ])
            ->add('info', null, [
                'label' => 'Description',
            ])
            ->add('location',LocationType::class, [
                'label' => 'Adresse',
                'label_attr'=> ['class'=> 'ville']
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
