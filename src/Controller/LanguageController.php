<?php

namespace App\Controller;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/language')]
#[IsGranted('ROLE_ADMIN')]
class LanguageController extends AbstractController
{
    #[Route('/', name: 'app_admin_language_index', methods: ['GET'])]
    public function index(LanguageRepository $languageRepository): Response
    {
        $languages = $languageRepository->findAll();

        return $this->render('admin/language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/new', name: 'app_admin_language_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $language = new Language();
        
        if ($request->isMethod('POST')) {
            $language->setName($request->request->get('name'));
            $entityManager->persist($language);
            $entityManager->flush();
            $this->addFlash('success', 'Langue créée avec succès.');
            return $this->redirectToRoute('app_admin_language_index');
        }

        return $this->render('admin/language/new.html.twig', [
            'language' => $language,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_language_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Language $language, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $language->setName($request->request->get('name'));
            $entityManager->flush();
            $this->addFlash('success', 'Langue modifiée avec succès.');
            return $this->redirectToRoute('app_admin_language_index');
        }

        return $this->render('admin/language/edit.html.twig', [
            'language' => $language,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_language_delete', methods: ['POST'])]
    public function delete(Request $request, Language $language, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$language->getId(), $request->request->get('_token'))) {
            $entityManager->remove($language);
            $entityManager->flush();
            $this->addFlash('success', 'Langue supprimée.');
        }

        return $this->redirectToRoute('app_admin_language_index');
    }
}
