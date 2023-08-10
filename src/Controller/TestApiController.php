<?php

namespace App\Controller;

use App\Controller\Api\CreatureController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestApiController extends AbstractController
{
    /**
     * @Route("/test/api", name="app_test_api")
     */
    public function index(CreatureController $cr): Response
    {
        return $this->render('test_api/index.html.twig', [
            'creatureController' => 'creatureController',
        ]);
    }
}
