<?php

namespace App\DataFixtures;

use App\DataFixtures\Creatures;
use App\Entity\Creature;
use App\Entity\Habitat;
use App\Entity\ProfilePicture;
use App\Entity\Type;
use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{

    private $connection;
    private $userPasswordHasher;


    public function __construct(Connection $connection, UserPasswordHasherInterface $userPasswordHasher) {
        // we get the BDD connection to communicate directly in SQL 
        $this->connection = $connection;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * Truncate tables to reset the id to 1
     */
    private function truncate() {
        // we pass in SQL language
        // desactivation of contrains verification FK
        $this->connection->executeQuery('SET foreign_key_checks = 0');
        // truncate
        $this->connection->executeQuery('TRUNCATE TABLE creature');
        $this->connection->executeQuery('TRUNCATE TABLE type');
        $this->connection->executeQuery('TRUNCATE TABLE habitat');
        $this->connection->executeQuery('TRUNCATE TABLE user');
        $this->connection->executeQuery('TRUNCATE TABLE profile_picture');
    }

    public function load(ObjectManager $manager): void
    {
        // truncate to reset the id count 
        $this->truncate();

        // instanciate datas
        $data = new Creatures;

        // creating types

        $types = [];
        for ($t = 0; $t < count($data->types); $t++) { 
            $type = new Type();
            
            $type->setName($data->types[$t]['name']);

            // persisting
            $manager->persist($type);

            $types[] = $type;
        }

        // creating habitats
        $habitats = [];
        for ($h = 0; $h < count($data->habitats); $h++) { 
            $habitat = new Habitat();
            
            $habitat->setName($data->habitats[$h]['name']);

            // persisting
            $manager->persist($habitat);

            $habitats[] = $habitat;
        }

        // creating creatures
        for ($i = 0; $i < count($data->creatures); $i++) {

            // instanciating empty creature object
            $creature = new Creature();

            $creature->setName($data->creatures[$i]['title']);
            $creature->setResume($data->creatures[$i]['summary']);
            $creature->setDescription($data->creatures[$i]['body']);
            $creature->setPicture($data->creatures[$i]['picture']);
            $creature->setSize($data->creatures[$i]['size']);
            $creature->setWeight($data->creatures[$i]['weight']);
            $creature->setPhysicalPeculiarities($data->creatures[$i]['physical_pecularities']);
            $creature->setDiet($data->creatures[$i]['diet']);
            $creature->setOrigin($data->creatures[$i]['origin']);
            $creature->setLocalisation($data->creatures[$i]['localisation']);
            $creature->setFirstMention($data->creatures[$i]['first_mention']);

            $int = mt_rand(1685577600,1688169600);
            $date = date_timestamp_set(new Datetime, $int);
            $creature->setCreatedAt($date);

            $creature->setIsValid('true');
            $creature->setIsVisible('true');

            $creature->setOtherNames($data->creatures[$i]['other_names']);
            $creature->setRelatedCreatures($data->creatures[$i]['related_creatures']);

            $slug = $this->createSlug($data->creatures[$i]['title']);
            $creature->setSlug($slug);

            // shuffle the type array to avoid double types in creature
            shuffle($types);
            
            for ($t = 1; $t <= mt_rand(1, 3); $t++) {

                $randomType = $types[$t];

            // we associate
            $creature->addTypes($randomType);
            // dump($creature->getTypes());
            }

            // shuffle the habitat array to avoid double types in creature
            shuffle($habitats);
            
            for ($t = 1; $t <= mt_rand(1, 2); $t++) {

                $randomHabitat = $habitats[$t];

            // we associate
            $creature->addHabitats($randomHabitat);
            // dump($creature->getTypes());
            }

            // persist
            $manager->persist($creature); 
        }

        // creating 3 profiles pictures
        for ($p = 0; $p < 4; $p++) {
            $profilePicture = new ProfilePicture();

            $profilePicture->setTitle('picture#' . $p);
            $profilePicture->setUrl('https://picsum.photos/200/300');
            $manager->persist($profilePicture);  
        }

        // Creating a User
        $admin = new User();
        $admin->setName('Jules');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->userPasswordHasher->hashPassword($admin, 'admin')
        );
        $admin->setIsValid('true');
        $admin->setPicture($profilePicture);
        $admin->setCreatedAt(new DateTime());
        
        $manager->persist($admin);

        // flush all the datas created to database
        $manager->flush();
    }

    public function createSlug($str, $delimiter = '-'){

        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    
    } 
}
