<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {

        $users = $userRepository -> findAll();
        return $this->render('user/index.html.twig', [
            // 'controller_name' => 'UserController',
            'users' => $users
        ]);
    }
    #[Route('/user/{id}', name: 'app_user_to-editor')]
    public function changeRole(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles(['ROLE_EDITOR', 'ROLE_USER']);
        $entityManager->flush();

        $this->addFlash('success', 'Le rôle de l\'utilisateur a été changé avec succès.');
        return $this->redirectToRoute('app_user');
    }
}