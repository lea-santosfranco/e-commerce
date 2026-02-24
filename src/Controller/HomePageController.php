<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(): Response
    {
        $StudentsNames = ['Alice', 'Bob', 'Charlie', 'David', 'Eve'];
        $StudentsAges = [20, 22, 17, 21, 23];
        return $this->render('home_page/index.html.twig', [
            // 'controller_name' => 'HomePageController',
            'StudentsNames' => $StudentsNames,
            'StudentsAges' => $StudentsAges
        ]);
    }
}
