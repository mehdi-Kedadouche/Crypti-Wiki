<?php

namespace App\Controller\Backoffice;

use App\Repository\CreatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends AbstractController
{
    // /**
    //  * @Route("/", name="app_home")
    //  */
    // public function home(CreatureRepository $cr): Response
    // {
    //     $creature = $cr->findAll();
    //     return $this->render('backoffice/home.html.twig', [
    //         'creatures' => $creature,
    //     ]);
    // }

    // /**
    //  * @Route("/articles", name="app_articles")
    //  */
    // public function articleList(CreatureRepository $cr): Response
    // {
    //     return $this->render('backoffice/articles/list.html.twig', [
    //         'creatures' => $cr->findAll(),
    //     ]);
    // }
}
