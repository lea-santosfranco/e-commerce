<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategorieFormType;
use App\Entity\Categorie;

final class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }
    #[Route('/categorie/new', name: 'app_categorie_new')]
    public function addCategorie(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category = new Categorie();

        $form = $this->createForm(CategorieFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
        }

        return $this->render('categorie/newCategorie.html.twig', [
            // 'controller_name' => 'CategorieController',
            "form" => $form->createView()
        ]);
    }

    #[Route('/categorie/update/{id}', name: 'app_categorie_update')]
    public function updateCategorie(EntityManagerInterface $entityManager, Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
                return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/updateCategorie.html.twig', [
            // 'controller_name' => 'CategorieController',
            "form" => $form->createView()
        ]);
    }
}
