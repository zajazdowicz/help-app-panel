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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class DreamFulfillmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('donorName', TextType::class, [
                'label' => 'Imię i nazwisko',
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Imię i nazwisko musi mieć co najmniej {{ limit }} znaki.',
                        'maxMessage' => 'Imię i nazwisko nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('donorEmail', EmailType::class, [
                'label' => 'Adres e‑mail',
                'required' => false,
                'constraints' => [
                    new Email(['message' => 'Podaj poprawny adres e‑mail.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'E‑mail nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('donorNickname', TextType::class, [
                'label' => 'Pseudonim (opcjonalnie)',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Pseudonim nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
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
                'constraints' => [
                    new NotBlank(['message' => 'Ilość sztuk jest wymagana.']),
                    new Positive(['message' => 'Ilość musi być większa od zera.']),
                    new Range([
                        'min' => 1,
                        'max' => 100,
                        'notInRangeMessage' => 'Ilość musi być między {{ min }} a {{ max }}.',
                    ]),
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Kwota darowizny (zł)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                    'min' => 0,
                ],
                'constraints' => [
                    new PositiveOrZero(['message' => 'Kwota nie może być ujemna.']),
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
