<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieFormType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController
{
    #[Route('/admin/categorie', name: 'app_categorie')]
    public function index(CategorieRepository $repo): Response
    {
        $categories = $repo->findAll();
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
            'categories' => $categories
        ]);
    }
    #[Route('/admin/categorie/new', name: 'app_categorie_new')]
    public function addCategorie(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category = new Categorie();

        $form = $this->createForm(CategorieFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', "La catégorie a été ajoutée avec succès !");
            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/newCategorie.html.twig', [
            // 'controller_name' => 'CategorieController',
            "form" => $form->createView()
        ]);
    }

    #[Route('/admin/categorie/update/{id}', name: 'app_categorie_update')]
    public function updateCategorie(EntityManagerInterface $entityManager, Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('info', "La catégorie a été mise à jour avec succès !");
                return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/updateCategorie.html.twig', [
            // 'controller_name' => 'CategorieController',
            "form" => $form->createView()
        ]);
    }
    #[Route('/admin/categorie/delete/{id}', name: 'app_categorie_delete')]
    public function deleteCategorie(EntityManagerInterface $entityManager, Categorie $categorie): Response
    {
        $entityManager->remove($categorie);
        $entityManager->flush();
        $this->addFlash('danger', "La catégorie a été supprimée avec succès !"); #addFlash() est une méthode de la classe AbstractController qui permet d'ajouter un message flash à la session. Le premier argument est le type du message (par exemple "success", "danger", "info"), et le deuxième argument est le contenu du message.
        return $this->redirectToRoute('app_categorie');
    }
}
