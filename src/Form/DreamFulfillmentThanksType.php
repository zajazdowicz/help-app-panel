<?php

namespace App\Form;

use App\Entity\DreamFulfillment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class DreamFulfillmentThanksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('childPhotoUrl', UrlType::class, [
                'label' => 'Link do zdjęcia dziecka z prezentem',
                'required' => false,
                'help' => 'Wklej bezpośredni link do zdjęcia (np. z Google Drive, Imgur).',
            ])
            ->add('childMessage', TextareaType::class, [
                'label' => 'Wiadomość podziękowania od dziecka',
                'required' => false,
                'attr' => ['rows' => 4],
                'help' => 'Krótka wiadomość od dziecka lub opiekuna.',
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
