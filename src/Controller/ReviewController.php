<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/review')]
class ReviewController extends AbstractController
{
    #[Route('/new/{book}', name: 'app_review_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $existingReview = $entityManager->getRepository(Review::class)->findOneBy([
            'user' => $user,
            'book' => $book,
        ]);

        if ($existingReview) {
            $this->addFlash('warning', 'Vous avez déjà noté ce livre.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $review = new Review();
        $review->setUser($user);
        $review->setBook($book);
        $review->setCreatedAt(new \DateTime());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Avis ajouté avec succès.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_review_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }

        return $this->redirectToRoute('app_book_show', ['id' => $review->getBook()->getId()]);
    }
}