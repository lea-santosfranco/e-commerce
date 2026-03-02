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
    #[Route('/user/{id}/to/editor', name: 'app_user_to-editor')]
    public function changeRoleToEditor(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles(['ROLE_EDITOR', 'ROLE_USER']);
        $entityManager->flush();

        $this->addFlash('success', 'Le rôle de l\'utilisateur a été changé avec succès.');
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/{id}/change-role/{role}', name: 'app_user_change_role')] //remettre la wildcard {role} pour selection du role en question
    public function changeRole(EntityManagerInterface $entityManager, User $user, $role): Response
    {
        /**
         * Permet à un admin de changer le rôle d'un utilisateur via un lien
         * Exemple: /admin/user/42/change-role/ROLE_EDITOR
         * 
         * @param EntityManagerInterface $entityManager Pour persister les changements en BDD
         * @param User $user L'utilisateur à modifier (récupéré automatiquement via param converter)
         * @param string $role Le rôle à attribuer (ROLE_EDITOR, ROLE_USER...)
         * @return Response Redirection vers la liste users avec message flash
         */
        
        // On définit une liste blanche des rôles valides pour éviter les abus
        $validRoles = ['ROLE_EDITOR', 'ROLE_USER'];

        // Vérification sécurité : rôle valide ?
        // in_array($role, $validRoles, true)
        // Vérifie si $role EST DANS le tableau $validRoles
        // true = comparaison stricte (type + valeur)
        // Retourne true ou false

        if (!in_array($role, $validRoles, true)) {
            $this->addFlash('error', "Le rôle demandé n'est pas valide.");
            return $this->redirectToRoute('app_user');
        }
        //sans le code precedent le !in_array qui verifie les roles donc mon code serai comme ceci 
        // Sans le !in_array()
        // public function changeRole(User $user, string $role) {
        //     $user->setRoles([$role, 'ROLE_USER']);  // ← N'IMPORTE QUEL RÔLE !
        //     $entityManager->flush();
        // }
        //Et bien en url on pourrait mettre un truc du style 
        //URL malveillante : /admin/user/42/change-role/ROLE_BYPASS_FIREWALL
        //il faudrait juste quil trouve la route de l'url et cest gagné
        //et au prochian login et bien l'id 42 aurai tout les dreoit d'admin 
        //sans que personne ne s'en rende compte, c'est pour cela que l'on verifie 
        //que le role est dans la liste blanche des roles valides avant de l'attribuer à l'utilisateur
        // On remplace complètement la liste des rôles par le rôle demandé plus éventuellement 
        // ROLE_USER par défaut
        if ($role !== 'ROLE_USER') {
            $user->setRoles([$role, 'ROLE_USER']);
        } else {
            $user->setRoles([$role]);
        }
        // Sauvegarde en base de données
        $entityManager->flush();
        // Message de confirmation
        $this->addFlash('success', "Le rôle $role a bien été attribué à l'utilisateur.");
        // Redirection vers la liste des utilisateurs
        return $this->redirectToRoute('app_user');
    }
     #[Route('/admin/user/{id}/remove/editor/role ', name: 'app_user_remove_editor_role')]
    public function removeRoleEditor(EntityManagerInterface $entityManager, User $user): Response
    {
        $user->setRoles([]);
        $entityManager->flush();

        $this->addFlash('danger', "Le rôle éditeur à bien été retiré à l'utilisateur");
        
        return $this->redirectToRoute('app_user');
    }
     #[Route('/admin/user/{id}/remove/', name: 'app_user_remove')]
    public function ruserRemove(EntityManagerInterface $entityManager,$id,  UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('danger', "L'utilisateur à bien été supprimé.");
        
        return $this->redirectToRoute('app_user');
    }
}