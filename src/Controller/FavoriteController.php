<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Favorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/favorite')]
class FavoriteController extends AbstractController
{
    #[Route('/toggle/{book}', name: 'app_favorite_toggle', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(Book $book, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'book' => $book,
        ]);

        if ($favorite) {
            $entityManager->remove($favorite);
            $this->addFlash('success', 'Retiré des favoris.');
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setBook($book);
            $entityManager->persist($favorite);
            $this->addFlash('success', 'Ajouté aux favoris.');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }
}