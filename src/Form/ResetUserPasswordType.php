<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ResetUserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plainPassword', PasswordType::class, [
            'label' => 'Nouveau mot de passe',
            'constraints' => [
                new NotBlank(message: 'Veuillez saisir un mot de passe.'),
                new Length(
                    min: 8,
                    max: 255,
                    minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                ),
            ],
            'attr' => [
                'autocomplete' => 'new-password',
            ],
        ]);
    }
}
