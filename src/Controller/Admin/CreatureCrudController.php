<?php

namespace App\Controller\Admin;

use App\Entity\Creature;


use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Validator\Constraints\Date;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreatureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Creature::class;
    }


    public function configureFields(string $pageName): iterable
    {


        $fields = [

            
            IdField::new('codeCreature' , 'code_creature'),
            TextField::new('name', 'Nom'),
            // TextareaField::new('resume', 'Résumé')->setMaxLength(100)->onlyOnIndex(),
            TextareaField::new('resume', 'Résumé')->hideOnIndex(),
            // TextareaField::new('description', 'Description')->setMaxLength(100)->onlyOnIndex(),
            TextareaField::new('description', 'Description')->hideOnIndex(),
            ImageField::new('picture', 'Image')->setBasePath('uploads/images')->hideOnForm(),
            
            TextField::new('picture', 'Image')->hideOnIndex(),
            TextField::new('size', 'Taille')->hideOnIndex(),
            TextField::new('weight', 'Poids')->hideOnIndex(),
            TextField::new('diet', 'Régime alimentaire')->hideOnIndex(),
            TextField::new('origin', 'Origine')->hideOnIndex(),
            TextField::new('localisation', 'Localisation')->hideOnIndex(),
            ArrayField::new('relatedCreatures')->hideOnIndex(),
            ArrayField::new('otherNames')->hideOnIndex(),
            TextField::new('firstMention', 'Premières mention')->hideOnIndex(),
            TextField::new('physicalPeculiarities', 'Particularités physiques')->hideOnIndex(),
            SlugField::new('slug', 'Slug')->setTargetFieldName('name')->hideOnIndex(),
            AssociationField::new('types', 'Types'),
            AssociationField::new('habitats', 'Habitats'),
            BooleanField::new('isValid')->hideOnIndex(),
            BooleanField::new('isVisible')->hideOnIndex(),
    
        ];



            if ($pageName === Crud::PAGE_INDEX) {
        
                $fields[] =  DateTimeField::new('createdAt', 'Créé le')->onlyOnIndex();
                $fields[] =  DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnIndex();
                $fields[0] = IdField::new('id');
                $fields[] =  NumberField::new('isValid')->onlyOnIndex();
                $fields[] =  NumberField::new('isVisible')->onlyOnIndex();
            }
            

        
        return $fields;
    }

        public function configureActions(Actions $actions): Actions
        {
            return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)

            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->addCssClass('btn btn-success'); // Customize the New button
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->addCssClass("btn btn-info"); // Customize the Show button
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->addCssClass('btn btn-warning'); // Customize the Edit button
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->addCssClass('btn btn-outline-danger'); // Customize the Delete button
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->addCssClass('btn btn-warning'); // Customize the Edit button in the Show view
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->addCssClass('btn btn-outline-danger '); // Customize the Delete button in the Show view
            });
        }

        public function configureFilters(Filters $filters): Filters
        {
            return $filters
            ->add('name');
        }

        public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
        {
            parent::persistEntity($entityManager, $entityInstance);
    
            // Ajouter un message flash après la création
            $this->addFlash('success', 'La créature a été créée avec succès.');
        }
    
        // Méthode pour gérer les événements avant la mise à jour d'une entité (modification)
        public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
        {
            parent::updateEntity($entityManager, $entityInstance);
    
            // Ajouter un message flash après la modification
            $this->addFlash('success', 'La créature a été modifiée avec succès.');
        }
}



// class CreatureCrudController extends AbstractCrudController
// {
//     public static function getEntityFqcn(): string
//     {
//         return 'App\Entity\Creature';
//     }

//     public function configureFields(Crud $crud): Crud
//     {
//         return $crud
//             ->addFields([
//                 IntegerField::new('id')->hideOnForm(),
//                 IntegerField::new('codeCreature')->hideOnForm(),
//                 TextField::new('name'),
//                 TextareaField::new('resume'),
//                 TextareaField::new('description'),
//                 ImageField::new('picture')->setBasePath('/uploads/pictures/')->onlyOnIndex(),
//                 ImageField::new('pictureFile')->setFormTypeOptions(['required' => false])->onlyOnForms(),
//                 ArrayField::new('otherNames'),
//                 AssociationField::new('types')->autocomplete(),
//                 ArrayField::new('relatedCreatures'),
//                 TextField::new('size'),
//                 TextField::new('weight'),
//                 TextField::new('physicalPeculiarities')->setFormTypeOptions(['required' => false]),
//                 TextField::new('diet'),
//                 AssociationField::new('habitats')->autocomplete(),
//                 TextField::new('origin'),
//                 TextField::new('localisation'),
//                 TextField::new('firstMention')->setFormTypeOptions(['required' => false]),
//                 DateTimeField::new('createdAt')->hideOnForm(),
//                 DateTimeField::new('updatedAt')->hideOnForm(),
//                 BooleanField::new('isValid'),
//                 BooleanField::new('isVisible'),
//                 AssociationField::new('user')->autocomplete(),
//                 SlugField::new('slug')->setTargetFieldName('name'),
//             ]);
//     }
// }

    // ImageField::new('pictureFile', 'Image')->setFormType(VichImageType::class)->setFormTypeOptions(['required' => false]),
            // TextField::new('picture')->setBasePath('/uploads/pictures/')->onlyOnIndex(),
            // ImageField::new('pictureFile')->setFormTypeOptions(['required' => false])->onlyOnForms(),
            //ImageField::new('picture', 'Image')->setBasePath('uploads/images')->onlyOnIndex(),
            // ->onlyOnIndex() Remplacez 'public/uploads/images' par le chemin du répertoire de téléchargement souhaité
            // , // Remplacez 'uploads/images' par le répertoire accessible depuis le web où les images téléchargées seront situées
            



