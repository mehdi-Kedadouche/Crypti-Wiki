<?php

namespace App\Controller\Api;

use DateTime;
use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProfilePictureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;




class UserController extends AbstractController
{

/**
 * API d'inscription d'un utilisateur
 * Method = POST
 * 
 * @Route("/api/v1/signup", name="api_signup", methods={"POST"})
 */

public function signup(MailerInterface $mailer ,Request $request ,EntityManagerInterface $em , ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher , UserRepository $ur , ProfilePictureRepository $ppr): Response
{
    // On récupére les données de la requête JSON
    $data = json_decode($request->getContent(), true);
    
    
    // On vérifie si les champs requis sont présents
    if (!isset($data['name'], $data['email'], $data['password'], $data['password_confirmation'], $data['profile_picture']) || empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['password_confirmation']) || empty($data['profile_picture']) ) {
        return new JsonResponse(['errorMessage' => 'Il faut que tous les champs soient remplis !'], 400);
    }

    //Vérifier email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return new JsonResponse(['errorMessage' => 'l\'email rentré n\'est pas valide !'], 400);
    }

    //Vérifier email unique
    $emailFind = $ur->findBy(['email' => $data['email']]);

    if($emailFind != null) {
        return new JsonResponse(['errorMessage' => 'l\'email rentré existe déjà !'], 400);
    }

    //Password : 8car min maj Chiffre
    $password = $data['password'];
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        return new JsonResponse(['errorMessage' => "Le mot de passe doit contenir au moins 8 caractères avec une majuscule et un chiffre."], 400);
    }

    //password = password_confirm
    if (!($password === $data['password_confirmation'])) {
        return new JsonResponse(['errorMessage' => "Erreur lors de la confirmation du mot de passe."], 400);
    } 

    //profile_picture bien verifier si l'id est bien présente en BDD
    $pictureIdFind = $ppr->findBy(['id' => $data['profile_picture']]);
    if($pictureIdFind == null ) {
        return new JsonResponse(['errorMessage' => 'La photo rentrée ne correspond pas !'], 400);
    }

    //faire un Hach de confirmation 
    //

    //  création d'un nouvel utilisateur
    $user = new User();
    $profilePictureFind = $ppr->find($data['profile_picture']);

    $user->setName($data['name']);
    $user->setEmail($data['email']);
    $user->setPicture($profilePictureFind);
    $plainPassword = $data['password'];

    // On hache le mot de passe
    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
    $user->setPassword($hashedPassword);

    // on met le isValid du User en true (EDIT : se met par défaut en true)
    $user->setIsValid(true);

    // On verifie les erreurs
    $errors = $validator->validate($user);
    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return new JsonResponse(['errors' => $errorMessages], 400);
    }
        

    // créer un hash "email_confirmation"
    $createdAt = $user->setCreatedAt(new DateTime());
    $createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');
    $hashContent = $data['email'] . '|' . $createdAt;
    $emailConfirmationHash = hash('sha256', $hashContent);

    // URL de confirmation contenant le hash généré
    $confirmationUrl = 'https://cryptiwiki/alwaysdata.net/confirmation/' . $emailConfirmationHash;

    $email = (new Email())
    ->from('noreply@cryptiwiki.com')
    ->to($user->getEmail())
    ->subject('Confirmation de l\'e-mail')
    ->html('Bonjour ' . $user->getName() . ',<br><br> Veuillez cliquer sur le lien suivant pour confirmer votre adresse e-mail :<br><a href="' . $confirmationUrl . '">Confirmer mon e-mail</a>');

    $mailer->send($email);

    //  On persist et on sauvegarde en BDD 
    $em->persist($user);
    $em->flush();

    // Retourner une réponse JSON
    return $this->json([
        'success' => true,
        'message' => 'Utilisateur enregistré avec succès !'
    ]);
}


/**
 * API pour la connexion d'un utilisateur
 * Method = POST
 *
 * @Route("/api/v1/login", name="api_login", methods={"POST"})
 */
public function login(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, UserRepository $ur, ParameterBagInterface $bag): JsonResponse
{
    //  On récupére les données de la requête
    $data = json_decode($request->getContent(), true);

    // On vérifie si les champs requis sont présents
    if (!isset($data['email']) || !isset($data['password'])) {
        return new JsonResponse(['errorMessage' => 'Merci de bien remplir les champs.'], 401);
    }

    // On récupère l'utilisateur à partir de l'adresse e-mail
    // $userRepository = $em->getRepository(User::class);
    $user = $ur->findOneBy(['email' => $data['email']]);

    // On vérifie si l'utilisateur existe et si le mot de passe correspond
    if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
        return new JsonResponse(['errorMessage' => 'Email et/ou password incorrect !'], 401);
    }

    // On vérifie si le compte est valide
    if (!$user->isIsValid()) {
        return new JsonResponse(['errorMessage' => 'Le compte n\'est pas validé, regardez vos emails !'], 401);
    }

    // je récupère la clé privée pour signer le token
    $privateKey = $bag->get('jwt_private_key');
      
    // Calcul de la date d'expiration (1 heure à partir de maintenant)
    $expiration_time = time() + 3600; // 3600 secondes = 1 heure

    // J'ajoute des informations au token
    $payload = [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'exp' => $expiration_time, // Date d'expiration du token
    ];
    
    // on crée le token
    try {

        $token = JWT::encode($payload, $privateKey, 'HS256');

    } catch (\Exception $e) {

        // Gérer les erreurs de décodage ici
        throw new \Exception('Erreur de d\'encodage du token : ' . $e->getMessage());

    }

    // On stocker le token en BDD 
    $user->setToken($token);
    $em->flush();

    //Si EMail & Password OK  On retourne une réponse JSON avec le nom du user et le token 
    return new JsonResponse(['name' => $user->getName(), 'token' => $token], 200);
}

/**
 * API pour récupérer la liste des avatars
 * Method = GET
 *
 * @Route("/api/v1/profile_pictures", name="api_profile_pictures", methods={"GET"})
 */
public function avatarsList( ProfilePictureRepository $ppr)

{
$avatarList = $ppr->findAll();


return $this->json(
    // La liste des créatures à sérialiser
    $avatarList,
    // Code de retour HTTP
    200,
    // Tableau des headers complémentaires à envoyer 
    // Avec la réponse
    [],

    ['groups' => 'get_picture']
);

}

}





