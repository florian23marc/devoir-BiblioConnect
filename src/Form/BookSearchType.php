<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', ChoiceType::class, [
                'label' => 'Titre du livre',
                'required' => false,
                'placeholder' => 'Sélectionner un titre...',
                'choices' => $options['titles'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('author', ChoiceType::class, [
                'label' => 'Auteur',
                'required' => false,
                'placeholder' => 'Sélectionner un auteur...',
                'choices' => $options['authors'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => 'Sélectionner une catégorie...',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'titles' => [],
            'authors' => [],
            'data_class' => null,
            'csrf_protection' => false,
        ]);
    }
}