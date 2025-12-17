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
            ])
            ->add('productUrl', UrlType::class, [
                'label' => 'Link do produktu',
                'required' => true,
            ])
            ->add('productPrice', TextType::class, [
                'label' => 'Cena',
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'class' => \App\Entity\Category::class,
                'label' => 'Kategoria',
                'required' => true,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz kategorię',
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
            ])
            ->add('quantityNeeded', IntegerType::class, [
                'label' => 'Potrzebna ilość',
                'required' => true,
                'attr' => ['min' => 1],
            ])
            ->add('isUrgent', ChoiceType::class, [
                'label' => 'Pilne',
                'choices' => [
                    'Tak' => true,
                    'Nie' => false,
                ],
                'required' => true,
            ])
            ->add('child', EntityType::class, [
                'class' => Child::class,
                'label' => 'Dziecko',
                'required' => true,
                'choice_label' => 'firstName',
                'placeholder' => 'Wybierz dziecko',
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
