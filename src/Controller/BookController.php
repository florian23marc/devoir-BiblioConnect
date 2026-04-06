<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookSearchType;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(Request $request, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les données pour les menus déroulants
        $titles = $bookRepository->findAllTitles();
        $authors = $bookRepository->findAllAuthors();
        $categories = $entityManager->getRepository(\App\Entity\Category::class)->findAll();

        // Créer le formulaire de recherche en GET
        $searchForm = $this->createForm(BookSearchType::class, null, [
            'method' => 'GET',
            'titles' => array_combine($titles, $titles) ?: [],
            'authors' => array_combine($authors, $authors) ?: [],
        ]);

        $searchForm->handleRequest($request);

        $formName = $searchForm->getName();
        $queryAll = $request->query->all();
        $queryData = [];

        if (isset($queryAll[$formName]) && is_array($queryAll[$formName])) {
            $queryData = $queryAll[$formName];
        } elseif (isset($queryAll['book_search']) && is_array($queryAll['book_search'])) {
            $queryData = $queryAll['book_search'];
        } elseif (isset($queryAll['book_search_type']) && is_array($queryAll['book_search_type'])) {
            $queryData = $queryAll['book_search_type'];
        }

        $search = isset($queryData['title']) && $queryData['title'] !== '' ? $queryData['title'] : null;
        $author = isset($queryData['author']) && $queryData['author'] !== '' ? $queryData['author'] : null;
        $category = null;

        if (isset($queryData['category']) && $queryData['category'] !== '') {
            if (is_numeric($queryData['category'])) {
                $categoryObj = $entityManager->getRepository(\App\Entity\Category::class)->find($queryData['category']);
                if ($categoryObj) {
                    $category = $categoryObj->getName();
                }
            } elseif (is_object($queryData['category'])) {
                $category = $queryData['category']->getName();
            }
        }

        $books = $bookRepository->findByFilters($search, $category, $author);

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Book $book, EntityManagerInterface $entityManager): Response
    {
        // Calculer le stock disponible en soustrayant les réservations actives
        $activeReservationsCount = $entityManager->getRepository(\App\Entity\Reservation::class)
            ->count(['book' => $book, 'status' => 'active']);
        
        $availableStock = $book->getStock() - $activeReservationsCount;

        return $this->render('book/show.html.twig', [
            'book' => $book,
            'availableStock' => max(0, $availableStock), // Ne pas afficher négatif
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function new(Request $request, EntityManagerInterface $entityManager, #[Autowire('%app.uploads_directory%')] string $appUploadsDirectory): Response
    {
        $book = new Book();
        $book->setCreatedAt(new \DateTime());
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', $originalFilename);
                $extension = $imageFile->getClientOriginalExtension() ?: 'jpg';
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;

                try {
                    $imageFile->move($appUploadsDirectory, $newFilename);
                    $book->setImage($newFilename);
                } catch (FileException $e) {
                    // handle exception if something happens during file upload
                }
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager, #[Autowire('%app.uploads_directory%')] string $appUploadsDirectory): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', $originalFilename);
                $extension = $imageFile->getClientOriginalExtension() ?: 'jpg';
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;

                try {
                    $imageFile->move($appUploadsDirectory, $newFilename);
                    $book->setImage($newFilename);
                } catch (FileException $e) {
                    // handle exception if something happens during file upload
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(new Expression("is_granted('ROLE_LIBRARIAN') or is_granted('ROLE_ADMIN')"))]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
            $this->addFlash('success', 'Livre supprimé.');
        }

        return $this->redirectToRoute('app_book_index');
    }
}