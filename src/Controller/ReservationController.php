<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/new/{book}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        // Calculer le stock disponible
        $activeReservationsCount = $entityManager->getRepository(Reservation::class)
            ->count(['book' => $book, 'status' => 'active']);
        $availableStock = $book->getStock() - $activeReservationsCount;

        // Check if user already has an active reservation for this book
        $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
            'user' => $this->getUser(),
            'book' => $book,
            'status' => ['pending', 'active']
        ]);

        if ($existingReservation) {
            $this->addFlash('warning', 'Vous avez déjà une réservation active pour ce livre.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        if ($availableStock <= 0) {
            $this->addFlash('danger', 'Ce livre n\'est pas disponible actuellement.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setBook($book);
        $reservation->setReservedAt(new \DateTime());
        $reservation->setStatus('pending');

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation supplémentaire des dates
            $startDate = $reservation->getStartDate();
            $endDate = $reservation->getEndDate();
            $dueDate = $reservation->getDueDate();
            $now = new \DateTime();

            // Vérifications de cohérence
            if ($startDate <= $now) {
                $form->get('startDate')->addError(new \Symfony\Component\Form\FormError('La date de début doit être dans le futur.'));
            }

            if ($endDate <= $startDate) {
                $form->get('endDate')->addError(new \Symfony\Component\Form\FormError('La date de fin doit être après la date de début.'));
            }

            // Durée maximale : 30 jours
            $interval = $startDate->diff($endDate);
            if ($interval->days > 30) {
                $form->get('endDate')->addError(new \Symfony\Component\Form\FormError('La durée maximale d\'emprunt est de 30 jours.'));
            }

            // Si dueDate est fournie, elle doit être après endDate
            if ($dueDate && $dueDate <= $endDate) {
                $form->get('dueDate')->addError(new \Symfony\Component\Form\FormError('La date de retour prévue doit être après la date de fin d\'emprunt.'));
            }

            // Si le formulaire est toujours valide après nos vérifications
            if ($form->isValid()) {
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->addFlash('success', 'Réservation effectuée avec succès ! Vous recevrez une confirmation lorsque votre réservation sera approuvée.');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'availableStock' => $availableStock,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('cancelled');
            $entityManager->flush();
            $this->addFlash('success', 'Réservation annulée.');
        }

        return $this->redirectToRoute('app_profile');
    }
}