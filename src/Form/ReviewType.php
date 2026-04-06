<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'label_attr' => [
                    'class' => 'form-label fw-bold',
                ],
                'choices' => [
                    '⭐ 1 étoile - Mauvais' => 1,
                    '⭐⭐ 2 étoiles - Faible' => 2,
                    '⭐⭐⭐ 3 étoiles - Moyen' => 3,
                    '⭐⭐⭐⭐ 4 étoiles - Bon' => 4,
                    '⭐⭐⭐⭐⭐ 5 étoiles - Excellent' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'rating-choice',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis',
                'label_attr' => [
                    'class' => 'form-label fw-bold',
                ],
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5',
                    'placeholder' => 'Partagez votre expérience avec ce livre...',
                ],
                'help' => 'Optionnel - Aidez les autres lecteurs avec votre avis détaillé',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}