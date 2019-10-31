<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserInfosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', SearchType::class,[
                'required' => false,
                'label' => 'Adresse mail',
                'attr' => [
                    'placeholder' => "Adresse mail"
                ]])
            ->add('username', SearchType::class,[
                'required' => false,
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'placeholder' => "Nom d'utilisateur"
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
