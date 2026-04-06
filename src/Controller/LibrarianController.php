<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/librarian')]
class LibrarianController extends AbstractController
{
    #[Route('/', name: 'app_librarian_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        $users = $entityManager->getRepository(User::class)->findAll();
        $books = $entityManager->getRepository(Book::class)->findAll();
        $reviews = $entityManager->getRepository(Review::class)->findAll();

        $totalBooks = count($books);
        $lowStockBooks = array_filter($books, function($book) use ($entityManager) {
            $activeReservationsCount = $entityManager->getRepository(Reservation::class)
                ->count(['book' => $book, 'status' => 'active']);
            $availableStock = $book->getStock() - $activeReservationsCount;
            return $availableStock <= 2;
        });
        $pendingReservations = array_filter($reservations, fn($res) => $res->getStatus() === 'pending');
        $activeReservations = array_filter($reservations, fn($res) => $res->getStatus() === 'active');

        return $this->render('librarian/dashboard.html.twig', [
            'reservations' => $reservations,
            'users' => $users,
            'books' => $books,
            'reviews' => $reviews,
            'totalBooks' => $totalBooks,
            'lowStockBooks' => $lowStockBooks,
            'pendingReservations' => count($pendingReservations),
            'activeReservations' => count($activeReservations),
        ]);
    }
}