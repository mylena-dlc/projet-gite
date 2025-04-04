<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Knp\Menu\FactoryInterface;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AuthenticatorInterface $authenticator, 
        EntityManagerInterface $entityManager,
        JWTService $jwt,
        FactoryInterface $factory,
        SluggerInterface $slugger,
        TranslatorInterface $translator): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Inscription'); 
        $slug = $slugger->slug('inscription');

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $translated = $translator->trans($error->getMessage(), [], 'validators');
                    $this->addFlash('error', $translated);
                }
            } else {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();
        
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $entityManager->persist($user);
                $entityManager->flush();
        
                $header = ['typ' => 'JWT', 'alg' => 'HS256'];
                $payload = ['user_id' => $user->getId()];
                $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
        
                $this->addFlash('success', 'Inscription validée !');
        
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request,
                );
            }
        }   
        
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'slug' => $slug,
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
