<?php

namespace App\Form;

use App\Entity\Dream;
use App\Entity\Child;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Range;

class DreamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $orphanage = $user ? $user->getOrphanage() : null;
        
        $builder
            ->add('productTitle', TextType::class, [
                'label' => 'Tytuł produktu',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Tytuł produktu jest wymagany.']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Tytuł musi mieć co najmniej {{ limit }} znaki.',
                        'maxMessage' => 'Tytuł nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('productUrl', UrlType::class, [
                'label' => 'Link do produktu (publiczny)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Link do produktu jest wymagany.']),
                    new Url(['message' => 'Podaj poprawny URL.']),
                ],
            ])
            ->add('originalProductUrl', UrlType::class, [
                'label' => 'Oryginalny link produktu (afiliacyjny)',
                'required' => false,
                'help' => 'Link bezpośredni do produktu w sklepie partnerskim',
                'constraints' => [
                    new Url(['message' => 'Podaj poprawny URL.']),
                ],
            ])
            ->add('affiliatePartner', ChoiceType::class, [
                'label' => 'Partner afiliacyjny',
                'choices' => [
                    'Brak' => null,
                    'Ceneo' => 'ceneo',
                    'Amazon' => 'amazon',
                    'Allegro' => 'allegro',
                    'Inny' => 'other',
                ],
                'required' => false,
                'placeholder' => 'Wybierz partnera',
            ])
            ->add('affiliateTrackingId', TextType::class, [
                'label' => 'ID śledzenia afiliacyjnego',
                'required' => false,
                'help' => 'Unikalny identyfikator z programu partnerskiego',
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'ID śledzenia nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('affiliateUrl', UrlType::class, [
                'label' => 'Wygenerowany link afiliacyjny',
                'required' => false,
                'help' => 'Link z kodem śledzącym, zostanie automatycznie wygenerowany jeśli pozostawisz puste',
                'constraints' => [
                    new Url(['message' => 'Podaj poprawny URL.']),
                ],
            ])
            ->add('productPrice', NumberType::class, [
                'label' => 'Cena (zł)',
                'required' => true,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => 0,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Cena jest wymagana.']),
                    new PositiveOrZero(['message' => 'Cena nie może być ujemna.']),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => \App\Entity\Category::class,
                'label' => 'Kategoria',
                'required' => true,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz kategorię',
                'constraints' => [
                    new NotBlank(['message' => 'Kategoria jest wymagana.']),
                ],
                'query_builder' => function (\App\Repository\CategoryRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis marzenia',
                'required' => true,
                'attr' => ['rows' => 4],
                'constraints' => [
                    new NotBlank(['message' => 'Opis jest wymagany.']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Opis nie może przekraczać {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('quantityNeeded', IntegerType::class, [
                'label' => 'Potrzebna ilość',
                'required' => true,
                'attr' => ['min' => 1, 'max' => 1000],
                'constraints' => [
                    new NotBlank(['message' => 'Ilość jest wymagana.']),
                    new Positive(['message' => 'Ilość musi być większa od zera.']),
                    new Range([
                        'min' => 1,
                        'max' => 1000,
                        'notInRangeMessage' => 'Ilość musi być między {{ min }} a {{ max }}.',
                    ]),
                ],
            ])
            ->add('isUrgent', ChoiceType::class, [
                'label' => 'Pilne',
                'choices' => [
                    'Tak' => true,
                    'Nie' => false,
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Wybierz, czy marzenie jest pilne.']),
                ],
            ])
            ->add('child', EntityType::class, [
                'class' => Child::class,
                'label' => 'Dziecko',
                'required' => true,
                'choice_label' => 'firstName',
                'placeholder' => 'Wybierz dziecko',
                'constraints' => [
                    new NotBlank(['message' => 'Wybierz dziecko.']),
                ],
                'query_builder' => function ($repository) use ($orphanage) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.orphanage = :orphanage')
                        ->setParameter('orphanage', $orphanage)
                        ->orderBy('c.firstName', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dream::class,
            'user' => null,
        ]);
        
        $resolver->setAllowedTypes('user', ['null', 'App\Entity\User']);
    }
}
