<?php

namespace App\Controller\Api;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\Creature;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\HabitatRepository;
use App\Repository\CreatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Controller\Api\ImageController;

class CreatureController extends AbstractController
{
    /**
     * API de récupération de l'ensemble des créatures
     * Method = GET, pas de paramètre
     *
     * @Route("/api/v1/creatures", name="api_creatures_get", methods={"GET"})
     */
    public function index(CreatureRepository $cr): Response
    {
        $creaturesList = $cr->findBy(['isValid'  => true]);

        return $this->json(
            // La liste des créatures à sérialiser
            $creaturesList,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_collection']
        );
    }

    /**
     * API de récupération de l'ensemble des créatures d'un nom donné
     * Method = GET, paramètre {slug}
     *
     * @Route("/api/v1/creatures/{slug}", name="api_creatures_get_item", methods={"GET"})
     */
    public function creatureSlug(CreatureRepository $cr, $slug): Response
    {
        $creature = $cr->findBy(['slug'=> $slug, 'isValid' => true]);

        if ($creature === null || $creature === []) {
            return $this->json([
                'error' => true,
                'message' => "La créature  demandée n'a pas été trouvée"
            ], 404, [], []);
        }

        return $this->json(
            // La liste des créatures à sérialiser
            $creature,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_item']
        );
    }

    /**
     * API de récupération d'une créature au hasard
     * Method = GET, pas de paramètre
     *
     * @Route("/api/v1/random", name="api_random", methods={"GET"})
     */
    public function randomCreature(CreatureRepository $cr): Response
    {

        // $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        // getting a random creature
        $randomId = $cr->getOneRandomCreature();

        $randomCreature = $cr->find($randomId);

        return $this->json(
            // La liste des créatures à sérialiser
            $randomCreature,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            // ['groups' => 'get_item']
        );
    }

    /**
     * API de récupération de la créature la plus récente
     * Method = GET, pas de paramètre
     *
     * @Route("/api/v1/last", name="api_last", methods={"GET"})
     */
    public function lastCreature(CreatureRepository $cr): Response
    {
        // On utilise le repository pour aller chercher une
        // créature random
        $lastId = $cr->getLastCreatedId();

        $lastCreature = $cr->find($lastId);

        return $this->json(
            // La liste des créatures à sérialiser
            $lastCreature,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_item']
        );
    }

    /**
     * API de recherche de créatures
     * Method = POST
     *
     * @Route("/api/v1/search", name="api_search", methods={"POST"})
     */
    public function search(Request $request, CreatureRepository $creatureRepository): Response
    {
        // Récupérer les données de recherche du corps de la requête JSON
        $data = json_decode($request->getContent(), true);

        // Récupérer les critères de recherche
        $searchTerm = $data['searchTerm'];

        // Effectuer la recherche dans le référentiel (repository) des créatures
        $results = $creatureRepository->searchCreatures($searchTerm);

        // Retourner les résultats de recherche en format JSON
        return $this->json(
            // La liste des créatures à sérialiser
            $results,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_item']
        );
    }

    /**
     * API de création d'une créature
     * Method = POST
     *
     * @Route("/api/v1/creatures/create", name="api_create_post", methods={"POST"})
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ParameterBagInterface $bag,
        CreatureRepository $cr,
        UserRepository $ur,
        TypeRepository $tr,
        HabitatRepository $hr,
        ImageController $imageController
    ): Response {
        if (!empty($request->headers->get('Authorization'))) {

            // avant tout, on récupère le token pour vérifier que le user est bien connecté
            $token = $request->headers->get('Authorization');

            // Si le token est transmis sous le format "Bearer <token>", on peut extraire uniquement le token lui-même
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            // la clé publique
            $privateKey = $bag->get('jwt_private_key');

            try {
                // Décode le token avec la clé secrète
                $decoded_token = JWT::decode($token, new Key($privateKey, 'HS256'));

                // Vérifie la date d'expiration
                if ($decoded_token->exp < time()) {
                    throw new \Exception('Token expiré');
                }

            } catch (\Firebase\JWT\ExpiredException $e) {
                throw new \Exception('Token expiré : ' . $e->getMessage());
            } catch (\Exception $e) {
                throw new \Exception('Erreur de décodage du token : ' . $e->getMessage());
            }

            // token datas
            $user_id = $decoded_token->id;
            $user_email = $decoded_token->email;
        } else {
            return $this->json(['errorMessage' => 'Token JWT non trouvé '], 401);
        }

        // on va chercher le user avec les infos du token
        $user = $ur->find($user_id);

        if($user->getEmail() === $user_email) {

            // On récupérer les données de la requête JSON
            $creature = $serializer->deserialize($request->getContent(), Creature::class, 'json');

            $data = json_decode($request->getContent());

            // je vérifie que l'image n'existe pas déjà en bdd 
            $testImage = $cr->findOneBy(['picture' => $data->picture]);

            if ($testImage) {
                $trigger = true;
            } else {
                $trigger = false;
            }

            $base64 = $data->base64;

            if(isset($data->base64) && !empty($base64)) {
                $picture = $data->picture;
                $imageController->resizeImageWithGD($bag, $base64, $picture);
            }

            // On verifie si il y a bien du contenu dans la requete
            if (empty($creature)) {
                return $this->json(['message' => 'Le contenu de la requête est vide ']);
            }

            // creating the slug
            $title = $creature->getName();
            $slug = strtolower(trim(preg_replace('/[\s-]+/', '-', preg_replace('/[^A-Za-z0-9-]+/', '-', preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $title))))), '-'));

            //setting slug
            $creature->setSlug($slug);

            // creating the code_creature
            $maxCodeCreature = $cr->getMaxCodeCreature();
            $creature->setCodeCreature($maxCodeCreature['maxCode'] +1);

            // setting created at
            $creature->setCreatedAt(new DateTime());

            // setting the user found from token on the created creature
            $creature->setUser($user);

            // Valider l'objet Creature
            $errors = $validator->validate($creature);

            if (count($errors) > 0) {
                // S'il y a des erreurs de validation, les retourner en tant que réponse JSON
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            // verifying that creature slug is not similar than database
            $slugCreature = $creature->getSlug();

            $creatureTest = $cr->findBy(['slug' => $slugCreature]);

            if ($creatureTest) {
                return $this->json(['message' => 'Le slug existe déjà en database']);
            }

            //setting types and habitats
            $data = json_decode($request->getContent(), true);
            if (isset($data['types'])) {
                foreach ($data['types'] as $typeJson) {
                    // Find the Type entity based on the ID
                    $type = $tr->find($typeJson['id']);
        
                    // Check if the Type entity is found
                    if ($type) {
                        // Add the Type entity to the creature's types collection
                        $creature->addTypes($type);
                    } else {
                        // Handle the case where the Type entity is not found, depending on your application's logic
                        // For example, you can return an error response or log the issue.
                    }
                }
            }
    
            if (isset($data['habitats'])) {
                foreach ($data['habitats'] as $habitatJson) {
                    // Find the Habitat entity based on the ID
                    $habitat = $hr->find($habitatJson['id']);
        
                    // Check if the Habitat entity is found
                    if ($habitat) {
                        // Add the Habitat entity to the creature's habitats collection
                        $creature->addHabitats($habitat);
                    } else {
                        // Handle the case where the Habitat entity is not found, depending on your application's logic
                        // For example, you can return an error response or log the issue.
                    }
                }
            }

            // Persist et flush l'objet Creature en base de données
            $entityManager->persist($creature);
            $entityManager->flush();
            // Retourner une réponse JSON avec l'objet Creature créé
            return $this->json($creature, Response::HTTP_CREATED);
        } else {
                return $this->json(['errorMessage' => 'Accès refusé : token non valide'], 401);
            }

            
    }

    /**
     * API de modification d'une créature
     * Method = PATCH
     * @Route("/api/v1/creatures/edit", name="api_edit", methods={"PATCH"})
     */
    public function edit(
        Request $request,
        ValidatorInterface $validator,
        TypeRepository $tr,
        HabitatRepository $hr,
        EntityManagerInterface $em,
        ParameterBagInterface $bag,
        UserRepository $ur,
        ImageController $imageController
    ): Response {

        if (!empty($request->headers->get('Authorization'))) {


            // on récupère le token pour vérifier que le user est bien connecté
            $token = $request->headers->get('Authorization');

            // Si le token est transmis sous le format "Bearer <token>", on peut extraire uniquement le token lui-même
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            // la clé publique
            $privateKey = $bag->get('jwt_private_key');

            try {
                // Décode le token avec la clé secrète
                $decoded_token = JWT::decode($token, new Key($privateKey, 'HS256'));

                // Vérifie la date d'expiration
                if ($decoded_token->exp < time()) {
                    throw new \Exception('Token expiré');
                }

            } catch (\Firebase\JWT\ExpiredException $e) {
                throw new \Exception('Token expiré : ' . $e->getMessage());
            } catch (\Exception $e) {
                throw new \Exception('Erreur de décodage du token : ' . $e->getMessage());
            }

            // token datas
            $user_id = $decoded_token->id;
            $user_email = $decoded_token->email;
        } else {
            return $this->json(['errorMessage' => 'Token JWT non trouvé '], 400);
        }

        // on va chercher le user avec les infos du token
        $user = $ur->find($user_id);

        if($user->getEmail() === $user_email) {

            // new creature created
            $newCreature = new Creature();

            // getting the data content of the request
            $data = json_decode($request->getContent());

            $base64 = $data->base64;

            if(isset($data->base64) && !empty($base64)) {
                $picture = $data->picture;
                $imageController->resizeImageWithGD($bag, $base64, $picture);
            }

            // error validator
            // $errors = $validator->validate($data, ['groups' => ['Default', 'get_collection', 'get_item']]);
            // if (count($errors) > 0) {
            //     return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
            // }

            try {
                // filling the creature with the new data from the request
                $newCreature->setName($data->name);
                $newCreature->setResume($data->resume);
                $newCreature->setDescription($data->description);
                $newCreature->setPicture($data->picture);
                $newCreature->setSize($data->size);
                $newCreature->setWeight($data->weight);
                $newCreature->setPhysicalPeculiarities($data->physicalPeculiarities);

                $newCreature->setWeight($data->weight);
                $newCreature->setDiet($data->diet);
                $newCreature->setOrigin($data->origin);
                $newCreature->setLocalisation($data->localisation);
                $newCreature->setFirstMention($data->firstMention);
                $newCreature->setOtherNames($data->otherNames);
                $newCreature->setRelatedCreatures($data->relatedCreatures);
                $newCreature->setUpdatedAt(new DateTime());
                $newCreature->setIsValid(false);
                $newCreature->setIsVisible(false);

                $newCreature->setCodeCreature($data->codeCreature);
                $newCreature->setCreatedAt(new DateTime());
                $newCreature->setUser($user);

            } catch (\Exception $e) {
                throw new \Exception('Erreur lors du edit : ' . $e->getMessage());
            }

            $data = json_decode($request->getContent(), true);

            // empty the currents habitats of the current creature
            foreach($newCreature->getHabitats() as $habitat) {
                $newCreature->removeHabitats($habitat);
            }

            // adding the new habitats
            foreach ($data['habitats'] as $habitatJson) {
                $habitat = $hr->find($habitatJson['id']);
                $newCreature->addHabitats($habitat);
            }

            // empty types of the current creature
            foreach($newCreature->getTypes() as $type) {
                $newCreature->removeTypes($type);

            }
            // adding new types from the front
            foreach ($data['types'] as $typeJson) {
                $type = $tr->find($typeJson['id']);
                $newCreature->addTypes($type);
            }

            // Persist creature object in database
            $em->persist($newCreature);
            $em->flush();
        
            // return JSON response with updated creature
            return $this->json($newCreature, Response::HTTP_OK, ['groups' => 'get_item']);
    } else {
        return $this->json(['errorMessage' => 'Token JWT non trouvé '], 400);
    }
    }
    /**
       * API de récupération de l'ensemble des Habitats
       * Method = GET, pas de paramètre
       *
       * @Route("/api/v1/habitats", name="api_habitats_get", methods={"GET"})
       */
    public function habitatList(HabitatRepository $hr): Response
    {
        $habitatsList = $hr->findAll();

        return $this->json(
            // La liste des créatures à sérialiser
            $habitatsList,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_collection']
        );
    }

    /**
     * API de récupération de l'ensemble des Types
     * Method = GET, pas de paramètre
     *
     * @Route("/api/v1/types", name="api_types_get", methods={"GET"})
     */
    public function typesList(TypeRepository $tr): Response
    {
        $typesList = $tr->findAll();

        return $this->json(
            // La liste des créatures à sérialiser
            $typesList,
            // Code de retour HTTP
            200,
            // Tableau des headers complémentaires à envoyer
            // Avec la réponse
            [],
            // Groupes a envoyer avec la réponse
            ['groups' => 'get_collection']
        );
    }

    /**
     * Méthode permettant de valider une créature editée
     * Method = PATCH
     * @Route("api/v1/creature/validate/{id}", name="api_creature_validate", methods={"PATCH"})
     */
    public function creatureValidation(CreatureRepository $cr, EntityManagerInterface $em, $id)
    {

        // on va chercher la creature à valider
        $creatureToValidate = $cr->find($id);

        // on va chercher toutes les creature avec le codeCreature de la creature à valider
        $codeCreature = $creatureToValidate->getCodeCreature();
        $listofCreatures = $cr->findBy(['codeCreature' => $codeCreature]);

        // si une des creature a le is_valid = true, on passe son visible à false, et son slug à null
        foreach ($listofCreatures as $creature) {
            if($creature->isIsVisible(true)) {
                $creature->setIsVisible(false);
                $creature->setSlug(null);
            }
        }

        $em->flush();

        // et on passe le is valid et is visible de la creature à valdier a true
        $creatureToValidate->setIsValid(true);
        $creatureToValidate->setIsVisible(true);

        // puis on lui crée son slug à partir de son titre
        $title = $creatureToValidate->getName();
        $slug = strtolower(trim(preg_replace('/[\s-]+/', '-', preg_replace('/[^A-Za-z0-9-]+/', '-', preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $title))))), '-'));
        $creatureToValidate->setSlug($slug);

        $em->flush();

        return $this->json(['response' => 'créature ' . $creatureToValidate->getId() . ' bien validée !'], 200);
    }


    // ROUTE  pour gérer la validation et le refus de la créature DANS LE BACKOFFICE
    /**
     * @Route("/validate-creature/{id}", name="validate_creature")
     */
    public function validateCreature(CreatureRepository $cr , EntityManagerInterface $em , $id): Response
    {
            // on va chercher la creature à valider 
            $creatureToValidate = $cr->find($id);

            // on va chercher toutes les creature avec le codeCreature de la creature à valider 
            $codeCreature = $creatureToValidate->getCodeCreature();
            $listofCreatures = $cr->findBy(['codeCreature' => $codeCreature]);
    
            // si une des creature a le is_valid = true, on passe son visible à false, et son slug à null
            foreach ($listofCreatures as $creature) {
                if($creature->isIsVisible(true)) {
                    $creature->setIsVisible(false);
                    $creature->setSlug(null);
                }
            }
    
            $em->flush();
    
            // et on passe le is valid et is visible de la creature à valdier a true 
            $creatureToValidate->setIsValid(true);
            $creatureToValidate->setIsVisible(true);
    
            // puis on lui crée son slug à partir de son titre 
            $title = $creatureToValidate->getName();
            $slug = strtolower(trim(preg_replace('/[\s-]+/', '-', preg_replace('/[^A-Za-z0-9-]+/', '-', preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $title))))), '-'));
            $creatureToValidate->setSlug($slug);
    
            $em->flush();

            //j'ajoute un message pour informer mon admin que l'article a bien été valider 
            $this->addFlash('success', 'L\'article a bien été validé !');

            //Je redirige l'administrateur vers la page "welcome"
            return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/refuse-creature/{id}", name="refuse_creature")
     */
    public function refuseCreature(CreatureRepository $cr , EntityManagerInterface $entityManager, $id): Response
    {
        $creature = $cr->find($id);
        $creature->setIsValid(false);
        $entityManager->persist($creature);
        $entityManager->flush();

        //j'ajoute un message pour informer mon admin que l'article a bien été valider 
        $this->addFlash('warning', 'L\'article n\'a pas été validé.');

        //Je redirige l'administrateur vers la page "welcome"
        return $this->redirectToRoute('admin');
    }
}



