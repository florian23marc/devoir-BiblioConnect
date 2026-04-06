<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateTimeType::class, [
                'label' => '📅 Date et heure de début d\'emprunt',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i'),
                ],
                'data' => new \DateTime('+1 hour'),
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => '📅 Date et heure de fin d\'emprunt',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime('+2 hours'))->format('Y-m-d\TH:i'),
                ],
                'data' => new \DateTime('+1 week'),
            ])
            ->add('dueDate', DateTimeType::class, [
                'label' => '📅 Date de retour prévue',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i'),
                ],
                'data' => new \DateTime('+2 weeks'),
                'help' => 'Optionnel : date à laquelle vous prévoyez de rendre le livre',
            ])
            ->add('submit', SubmitType::class, [
                'label' => '✅ Confirmer la réservation',
                'attr' => [
                    'class' => 'btn btn-success btn-lg w-100',
                ],
            ])
        ;

        // Validation des dates
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $reservation = $event->getData();
            $form = $event->getForm();

            if (!$reservation) {
                return;
            }

            $startDate = $reservation->getStartDate();
            $endDate = $reservation->getEndDate();
            $dueDate = $reservation->getDueDate();
            $now = new \DateTime();

            // La date de début doit être dans le futur
            if ($startDate && $startDate <= $now) {
                $form->get('startDate')->addError(new FormError('La date de début doit être dans le futur.'));
            }

            // La date de fin doit être après la date de début
            if ($startDate && $endDate && $endDate <= $startDate) {
                $form->get('endDate')->addError(new FormError('La date de fin doit être après la date de début.'));
            }

            // La date de retour prévue doit être après la date de fin d'emprunt
            if ($endDate && $dueDate && $dueDate <= $endDate) {
                $form->get('dueDate')->addError(new FormError('La date de retour prévue doit être après la date de fin d\'emprunt.'));
            }

            // Durée maximale d'emprunt : 30 jours
            if ($startDate && $endDate) {
                $interval = $startDate->diff($endDate);
                if ($interval->days > 30) {
                    $form->get('endDate')->addError(new FormError('La durée maximale d\'emprunt est de 30 jours.'));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}