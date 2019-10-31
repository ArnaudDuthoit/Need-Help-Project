<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_password', PasswordType::class, [
                'required' => false,
                'label' => 'Ancien mot de passe',
                'attr' => [
                    'placeholder' => "Ancien mot de passse"
                ]])
            ->add('new_password', PasswordType::class, [
                'required' => false,
                'label' => 'Nouveau mot de passe',
                'attr' => [
                    'placeholder' => "Nouveau mot de passse"
                ]])
            ->add('confirm_new_password', PasswordType::class, [
                'required' => false,
                'label' => 'Confimer nouveau mot de passe',
                'attr' => [
                    'placeholder' => "Confirmer nouveau mot de passse"
                ]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
