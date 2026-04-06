<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\Review;
use App\Entity\User;
use App\Form\UserRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        $users = $entityManager->getRepository(User::class)->findAll();
        $reviews = $entityManager->getRepository(Review::class)->findAll();
        $books = $entityManager->getRepository(Book::class)->findAll();
        
        // Calcul des stocks basé sur le stock disponible (stock - réservations actives)
        $totalBooks = count($books);
        $lowStockBooks = array_filter($books, function($book) use ($entityManager) {
            $activeReservationsCount = $entityManager->getRepository(Reservation::class)
                ->count(['book' => $book, 'status' => 'active']);
            $availableStock = $book->getStock() - $activeReservationsCount;
            return $availableStock <= 2;
        });
        $pendingReservations = array_filter($reservations, fn($res) => $res->getStatus() === 'pending');
        $activeReservations = array_filter($reservations, fn($res) => $res->getStatus() === 'active');

        return $this->render('admin/dashboard.html.twig', [
            'reservations' => array_filter($reservations, fn($res) => in_array($res->getStatus(), ['pending', 'active'])),
            'users' => $users,
            'reviews' => $reviews,
            'books' => $books,
            'totalBooks' => $totalBooks,
            'lowStockBooks' => $lowStockBooks,
            'pendingReservations' => count($pendingReservations),
            'activeReservations' => count($activeReservations),
        ]);
    }

    #[Route('/user/{id}/roles', name: 'app_admin_user_roles', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editUserRoles(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Rôles mis à jour.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/edit_user_roles.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/reservation/{id}/approve', name: 'app_admin_reservation_approve', methods: ['POST'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function approveReservation(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('approve'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('active');
            $reservation->setStartDate(new \DateTime());
            $book = $reservation->getBook();
            $book->setStock($book->getStock() - 1);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation approuvée.');
        }

        return $this->redirectToRoute($this->getDashboardRoute());
    }

    #[Route('/reservation/{id}/reject', name: 'app_admin_reservation_reject', methods: ['POST'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function rejectReservation(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('reject'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('rejected');
            $entityManager->flush();
            $this->addFlash('success', 'Réservation rejetée.');
        }

        return $this->redirectToRoute($this->getDashboardRoute());
    }

    #[Route('/reservation/{id}/return', name: 'app_admin_reservation_return', methods: ['POST'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function returnReservation(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('return'.$reservation->getId(), $request->request->get('_token'))) {
            // Vérifier que la réservation est active et que le livre a été emprunté
            if ($reservation->getStatus() !== 'active') {
                $this->addFlash('error', 'Cette réservation n\'est pas active.');
                return $this->redirectToRoute($this->getDashboardRoute());
            }

            if (!$reservation->getStartDate()) {
                $this->addFlash('error', 'Ce livre n\'a pas encore été emprunté.');
                return $this->redirectToRoute($this->getDashboardRoute());
            }

            $reservation->setStatus('returned');
            $reservation->setEndDate(new \DateTime()); // Enregistrer la date de retour réelle

            $book = $reservation->getBook();
            $book->setStock($book->getStock() + 1);

            $entityManager->flush();
            $this->addFlash('success', 'Livre retourné avec succès.');
        }

        return $this->redirectToRoute($this->getDashboardRoute());
    }

    private function getDashboardRoute(): string
    {
        return $this->isGranted('ROLE_ADMIN') ? 'app_admin_dashboard' : 'app_librarian_dashboard';
    }
}