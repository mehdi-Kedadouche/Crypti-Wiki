<?php

namespace App\Controller\Admin;

use App\Entity\Type;
use App\Entity\User;
use App\Entity\Habitat;
use App\Entity\Creature;
use App\Repository\CreatureRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function home(CreatureRepository $creatureRepository): Response
    {
        $creatures = $creatureRepository->findAll();
        
        return $this->render('easyAdmin/welcome.html.twig' ,['creatures' => $creatures ]);

        // return $this->render('@EasyAdmin/home.html.twig', [
        //     'creatures' => $creatures,
        // ]);
    }

    /**
     * @Route("/view-creature/{id}", name="view_creature")
     */
    public function viewCreature(CreatureRepository $creatureRepository , $id): Response
    {
        $creature = $creatureRepository->find($id);
        return $this->render('easyAdmin/view_creature.html.twig', [
            'creature' => $creature,
        ]);
    }

    
    

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
        ->setTitle('<span style="font-size: 24px; font-weight: bold;">CryptiWiki BackOffice</span>');
            
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau', 'fas fa-home')->setCssClass('custom-dashboard-item');
        yield MenuItem::linkToCrud('Articles', 'fas fa-ghost', Creature::class)->setCssClass('custom-articles-item');
        yield MenuItem::linkToCrud('Types', 'fas fa-list', Type::class)->setCssClass('custom-types-item');
        yield MenuItem::linkToCrud('Habitats', 'fas fa-igloo', Habitat::class)->setCssClass('custom-habitats-item');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class)->setCssClass('custom-users-item');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/easyadmin.css');
    }

    }
    
    

