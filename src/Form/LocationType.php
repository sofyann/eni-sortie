<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du lieu',
            ])
            ->add('street', null, [
                'label' => 'adresse',
            ])
            ->add('lat', null, [
                'label' => 'Latitude',
            ])
            ->add('lng', null, [
                'label' => 'longitude',
            ])
            ->add('city',CityType::class, [
                'label' => false,

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
