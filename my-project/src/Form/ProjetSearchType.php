<?php

namespace App\Form;

use App\Entity\ProjetSearch;

use App\Entity\Tag;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projectname', SearchType::class,[
                'required' => false,
                'label' => 'Nom du projet',
                'attr' => [
                    'placeholder' => "Nom du projet"
                ]
            ])
            ->add('tags', EntityType::class,[
                'required' => false,
                'label' => false,
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetSearch::class,
            'method' => 'get',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
