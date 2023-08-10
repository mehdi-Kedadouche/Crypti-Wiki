<?php

namespace App\Controller;

use App\Repository\CreatureRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestTypeController extends AbstractController
{
    /**
     * @Route("/test/type", name="app_test_type")
     */
    public function index(CreatureRepository $cr, UserRepository $ur): Response
    {
        return $this->render('test_type/index.html.twig', [
            'controller_name' => 'TestTypeController',
            'creature' => $cr->findAll(),
            'users' => $ur->findAll(),
        ]);
    }
}
