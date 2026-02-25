<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

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
    public function addCategorie(EntityManagerInterface $entityManager): Response
    {
        return $this->render('categorie/newCategorie.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }
}
