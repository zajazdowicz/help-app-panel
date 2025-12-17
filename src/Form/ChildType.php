<?php

namespace App\Form;

use App\Entity\Child;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Imię',
                'required' => true,
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Wiek',
                'required' => true,
                'attr' => ['min' => 0, 'max' => 18],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => true,
                'attr' => ['rows' => 4],
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Zweryfikowane',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
