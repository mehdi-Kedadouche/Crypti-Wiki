<?php

namespace App\Controller\Admin;

use App\Entity\Habitat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class HabitatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Habitat::class;
    }

    
    public function configureFields(string $pageName): iterable
    { 
        $fields = [
            TextField::new('name'),
            AssociationField::new('creatures', 'Créatures')->formatValue(function ($value, $entity) {
                $creatureNames = [];
                foreach ($entity->getCreatures() as $creature) {
                    $creatureNames[] = $creature->getName();
                }
                return implode(', ', $creatureNames);
            }),
        ];

        return $fields;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->update(Crud::PAGE_INDEX,Action::NEW,function(Action $action){
            return $action->setIcon('fa fa-plus')->addCssClass('btn btn-success');
        });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('name');
    }

}
