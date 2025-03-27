<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Knp\Menu\FactoryInterface;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface; 
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AuthenticatorInterface $authenticator, 
        EntityManagerInterface $entityManager,
        JWTService $jwt,
        SendEmailService $mail,
        Security $security,
        FactoryInterface $factory,
        SluggerInterface $slugger): Response

    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Inscription'); 
        $slug = $slugger->slug('inscription');

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                // Enregistrer l'utilisateur en base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Générer le token JWT
                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                $payload = [
                    'user_id' => $user->getId()
                ];

                $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

                // // Encodage du logo
                // $logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/img/logo-gite-rain-du-pair.png');

                // // Envoyer l'e-mail de confirmation
                // $mail->send(
                //     'no-reply@giteraindupair',
                //     $user->getEmail(),
                //     'Activation de votre compte sur le site Gîte du Rain du Pair',
                //     'register',
                //     compact('user', 'token', 'logo')
                // );

                // $this->addFlash('success', 'Utilisateur inscrit, veuillez cliquer sur le lien reçu pour confirmer votre adresse e-mail');

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request,
                );
            
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'slug' => $slug,
        ]);
    }

    #[Route('/verification/{token}', name: 'verify_user')]
    public function verifUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        // On vérifie si le token est valide (cohérent, pas expiré et signature correcte)
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            // Le token est valide
            // On récupère les données (payload)
            $payload = $jwt->getPayload($token);
            
            // On récupère le user
            $user = $userRepository->find($payload['user_id']);

            // On vérifie qu'on a bien un user et qu'il n'est pas déjà activé
            if($user && !$user->isVerified()){
                $user->setIsVerified(true);
                $em->flush();

                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_home');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

        // Fonction pour encoder le logo
        private function imageToBase64($path) {
            $path = $path;
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
        }

}
