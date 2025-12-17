<?php

namespace App\Form;

use App\Entity\DreamFulfillment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DreamFulfillmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('donorName', TextType::class, [
                'label' => 'Imię i nazwisko',
                'required' => false,
            ])
            ->add('donorEmail', EmailType::class, [
                'label' => 'Adres e‑mail',
                'required' => false,
            ])
            ->add('donorNickname', TextType::class, [
                'label' => 'Pseudonim (opcjonalnie)',
                'required' => false,
            ])
            ->add('isAnonymous', CheckboxType::class, [
                'label' => 'Chcę pozostać anonimowy',
                'required' => false,
            ])
            ->add('quantityFulfilled', IntegerType::class, [
                'label' => 'Ilość sztuk',
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Kwota darowizny (zł)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DreamFulfillment::class,
        ]);
    }
}
