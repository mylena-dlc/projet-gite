<?php


namespace App\Controller;

use Stripe\Stripe;
use App\Entity\User;
use App\Form\EmailUserType;
use App\Service\JWTService;
use App\Service\DompdfService;
use App\Service\RefundService;
use Knp\Menu\FactoryInterface;
use App\Form\ChangePasswordType;
use App\Service\SendEmailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
    ];

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, Security $security, FactoryInterface $factory, SluggerInterface $slugger): Response
    
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Connexion');  
        $slug = $slugger->slug('connexion');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'breadcrumb' => $breadcrumb,
            'slug' => $slug,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_reset_password')]
    public function forgottenPassword(Request $request, UserRepository $userRepository, JWTService $jwt, SendEmailService $mail) : Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handlerequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            // Le formulaire est envoyé ET valide
            // On recherche l'utilisateur
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if($user) {
                // On a un utilisateur
                // On génère un JWT
                // Header
                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                // Payload
                $payload = [
                    'user_id' => $user->getId()
                ];

                // On génère le token
                $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

                // On génère l'URL vers reset_password
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Encodage du logo
                $logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/img/logo-gite-rain-du-pair.png');

                // Envoyer l'e-mail
                $mail->send(
                    'no-reply@giteraindupair',
                    $user->getEmail(),
                    'Récupération de mot de passe sur le site Gîte Rain du Pair',
                    'reset_password',
                    compact('user', 'url', 'logo') 
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }

            // $user est null
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }
            return $this->render('security/reset_password_request.html.twig', [
                'resetPassRequestForm' => $form->createView()
            ]);
    }

        #[Route('/mot-de-passe-oublie/{token}', name: 'reset_password')]
        public function resetPassword(
            $token,
            JWTService $jwt,
            UserRepository $userRepository,
            Request $request,
            UserPasswordHasherInterface $passwordHasher,
            EntityManagerInterface $em,
        ): Response
        {
            
            // On vérifie si le token est valide (cohérent, pas expiré et signature correcte)
            if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
                // Le token est valide
                // On récupère les données (payload)
                $payload = $jwt->getPayload($token);
                
                
                // On récupère le user
                $user = $userRepository->find($payload['user_id']);
    
                if($user){
                    $form = $this->createForm(ResetPasswordFormType::class);
    
                    $form->handleRequest($request);
    
                    if($form->isSubmitted() && $form->isValid()){
                        $user->setPassword(
                            $passwordHasher->hashPassword($user, $form->get('password')->getData())
                        );
    
                        $em->flush();
    
                        $this->addFlash('success', 'Mot de passe changé avec succès');
                        return $this->redirectToRoute('app_login');
                    }
                    return $this->render('security/reset_password.html.twig', [
                        'passForm' => $form->createView()
                    ]);
                }
            }
            $this->addFlash('danger', 'Le token est invalide ou a expiré');
            return $this->redirectToRoute('app_login');
        }


    #[Route("/connexion/{service}", name: 'auth_oauth_connect', methods: ['GET'])]
    public function connect(string $service, ClientRegistry $clientRegistry): RedirectResponse
    {
        if (! in_array($service, array_keys(self::SCOPES), true)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry
            ->getClient($service)
            ->redirect(self::SCOPES[$service]);
    }

    #[Route('/oauth/check/{service}', name: 'auth_oauth_check', methods: ['GET', 'POST'])]
    public function check(): Response
    {
        return new Response(status: 200);
    }


    /**
    * Fonction pour afficher les détails d'un profil
    */   
    #[Route('gite-compte/{id}', name: 'app_profil')]
    public function profil(User $user, ReservationRepository $reservationRepository, ReviewRepository $reviewRepository): Response
    {
        $userSession = $this->getUser();

        if($userSession == $user) {
            // Recherche s'il y a une réservation en cours
            $ongoingReservation = $reservationRepository->findOngoingReservationForUser($user); 

            // Recherche toutes les réservations à venir
            $upcomingReservations = $reservationRepository->findUserUpcomingReservations($user);

            // Recherche toutes les réservations passées
            $previousReservations = $reservationRepository->findUserPreviousReservations($user);
            // Obtenir les id des réservations ayant un avis
            $reviews = $reviewRepository->findBy(['user' => $user]);
            $reviewedReservationIds = array_map(function ($review) {
                return $review->getReservation()->getId();
            }, $reviews);

            // Recherche toutes les réservations à confirmer par l'admin
            $reservationsToConfirms = $reservationRepository->findRequestForUser($user);

            // Recherche toutes les réservations refusées par l'admin
            $reservationsRefuseds = $reservationRepository->findReservationRefusedForUser($user);

            // Recherche toutes les réservations annulées
            $reservationsCancelled = $reservationRepository->findReservationCancelledForUser($user);

            // Déterminer la catégorie à afficher par défaut
            $defaultCategory = null;
            if (!empty($reservationsToConfirms)) {
                $defaultCategory = 'confirm';
            } elseif (!empty($ongoingReservation)) {
                $defaultCategory = 'ongoing';
            } elseif (!empty($upcomingReservations)) {
                $defaultCategory = 'upcoming';
            }

            return $this->render('security/profil.html.twig', [
                'user' => $user,
                'upcomingReservations' => $upcomingReservations,
                'previousReservations' => $previousReservations,
                'reviewedReservationIds' => $reviewedReservationIds,
                'ongoingReservation' => $ongoingReservation,
                'reservationsToConfirms' => $reservationsToConfirms,
                'reservationsRefuseds' => $reservationsRefuseds,
                'reservationsCancelled' => $reservationsCancelled,
                'defaultCategory' => $defaultCategory,
            ]);  
        }

        if($userSession != $user) {
            $this->addFlash('error', 'Accès refusé');
            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_login');
    }


    /**
    * Fonction pour afficher les détails d'une réservation
    */
    #[Route('gite-compte/reservation/{slug}', name: 'profil_reservation')]
    #[Route('gite-compte/reservation/{slug}/export', name: 'profil_reservation_export')]
    public function showReservation(ReservationRepository $reservationRepository, string $slug, DompdfService $dompdfService,  Request $request, ReviewRepository $reviewRepository): Response
    {
        // Récupérer la réservation
        $reservation = $reservationRepository->findOneBy(['slug' => $slug]);
        $gite = $reservation->getGite();

        // Récupérer un avis s'il existe
        $review = $reviewRepository->findOneBy(['Reservation' => $reservation]);

        // Si le paramètre "export" est présent, générer le PDF
        if ($request->attributes->get('_route') === 'admin_reservation_export') {
            $html = $this->renderView('reservation/invoice.html.twig', [
                'reservation' => $reservation,
                'gite' => $gite,
                'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') 
                . '/public/assets/img/logo-gite-rain-du-pair.png'),
            ]);

            // Générer le PDF à partir du HTML
            $pdfContent = $dompdfService->generatePdf($html);

            // Création d'une réponse de téléchargement
            $reference = preg_replace('/[^a-zA-Z0-9_-]/', '', $reservation->getReference());
            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename=FACTURE-' . $reference . '.pdf');
            return $response;
        }

        return $this->render('security/profil_reservation.html.twig', [
            'reservation' => $reservation,
            'review' => $review
        ]);
    }


    /**
    * Fonction pour afficher les détails d'un compte, modifier le mail et le mot de passe
    */   
    #[Route('gite-compte/{id}/menu', name: 'app_profil_account')]
    public function profilAccount(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        // Récupérer l'utilisateur connecté
        $userSession = $this->getUser();

        // Vérifier si l'utilisateur est connecté et autorisé
        if (!$userSession || $userSession !== $user) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_login');
        }

        // Formulaire pour modifier l'email
        $emailForm = $this->createForm(EmailUserType::class, $user);
        $emailForm->handleRequest($request);

        // Traiter le formulaire s'il est soumis et valide
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $em->flush(); // Sauvegarder les modifications en base de données
            $this->addFlash('success', 'Votre adresse email a été mise à jour.');

            return $this->redirectToRoute('app_profil_account', ['id' => $user->getId()]);
        }

        // Formulaire pour changer le mot de passe
        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('currentPassword')->getData();
            $newPassword = $passwordForm->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $em->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');
                return $this->redirectToRoute('app_profil_account', ['id' => $user->getId()]);
            }
        }

        // Afficher la page du profil avec le formulaire
        return $this->render('security/profil_account.html.twig', [
            'user' => $user,
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }
    


    /**
    * Fonction pour encoder le logo
    */
    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }


    /**
    * Fonction de suppresion d'un compte user
    */
    #[Route('gite-compte/{id}/supprimer', name: 'delete_account')]
    public function deleteAccount(User $user,
    ReservationRepository $reservationRepository,
    EntityManagerInterface $em,
    ReviewRepository $reviewRepository): Response
    {
        // Récupération de l'utilisateur actuellement connecté
        $user = $this->getUser();

        if ($user) {
            // Token pour identifier de manière unique chaque compte supprimé
            $deleteToken = md5(uniqid());

            // Mise à jour des champs de l'utilisateur
            $user->setEmail('utilisateur_supprime_' . $deleteToken);
            $user->setRoles(['role_supprime']);

            // Mise à jour du mot de passe
            $randomPassword = bin2hex(random_bytes(8));  // Générer une chaîne de caractères aléatoire
            $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
            $user->setPassword($passwordHash);

            // Mise à jour l'ID de l'utilisateur à NULL pour chaque réservation
            $reservations = $reservationRepository->findBy(['user' => $user]);
            foreach ($reservations as $reservation) {
                $reservation->setUser(null);
                $em->persist($reservation);
            }

            // Récupération des avis de l'utilisateur et suppression
            $reviews = $reviewRepository->findBy(['user' => $user]);
            foreach ($reviews as $review) {
                $em->remove($review); 
            }

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Impossible de supprimer le compte. Utilisateur non trouvé.');
        }

         return $this->redirectToRoute('app_home');
    }


    /**
    * Fonction d'annulation de réservation
    */
    #[Route('/reservation/{slug}/annulation', name: 'cancel_reservation')]
    public function cancelReservation(string $slug,
    ReservationRepository $reservationRepository, 
    EntityManagerInterface $em,
    SendEmailService $mail,
    RefundService $refundService
    ): Response
    {
        // Récupérer la réservation 
        $reservation = $reservationRepository->findOneBy(['slug' => $slug]);

         // Vérifier le statut de la réservation
        if ($reservation->getIsConfirm()['status'] !== 'confirmée') {
            $this->addFlash('error', 'Seules les réservations confirmées peuvent être annulées.');
            return $this->redirectToRoute('app_home');
        }

        // Calculer le nombre de jours avant l'arrivée
        $today = new \DateTime();
        $arrivalDate = $reservation->getArrivalDate();
        $interval = $today->diff($arrivalDate);
        $daysBeforeArrival = $interval->days;

        // Appliquer la politique de remboursement
        $refundPercentage = 0; 

        if ($daysBeforeArrival >= 5) {
            $refundPercentage = 100; // Remboursement intégral
        } elseif ($daysBeforeArrival > 0) {
            $refundPercentage = 50; // Remboursement de 50%
        } else {
            $refundPercentage = 0; // Aucun remboursement
        }

         // Calculer le montant remboursé
        $totalPrice = $reservation->getTotalPrice();
        $refundAmount = ($totalPrice * $refundPercentage) / 100;

        // Récupérer la méthode de paiement et l'id du paiement
        $paymentMethod = $reservation->getPaymentMethod();
        $paymentIntentId = $reservation->getStripePaymentId();

        try {
            if ($paymentMethod === 'stripe') {
                $refundService->processStripeRefund($paymentIntentId, $refundAmount);
            } else {
                throw new \Exception('Méthode de paiement inconnue.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du traitement du remboursement, veuillez contacter le propriétaire ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        // Mettre à jour le statut de la réservation
        $reservation->setIsConfirm(['status' => 'annulée']);
        $em->persist($reservation);
        $em->flush();

         // Envoyer le mail d'annulation
         $mail->send(
            'contact@gite-rain-du-pair.fr',
            $reservation->getEmail(), 
            '[GITE RAIN DU PAIR] Annulation de réservation',
            'cancel_reservation',
            [
                'reservation' => $reservation,
                'daysBeforeArrival' => $daysBeforeArrival,
                'refundPercentage' => $refundPercentage,
                'refundAmount' => $refundAmount,
                'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') 
                . '/public/assets/img/logo-gite-rain-du-pair.png'),
            ],
        );

        // Envoyer un e-mail à l'administrateur
        $mail->sendAdminNotification(
            'contact@gite-rain-du-pair.fr',
            'admin@giteraindupair.com',
            'Annulation de réservation',
            'admin_cancel_reservation',
            [
                'reservation' => $reservation,
                'daysBeforeArrival' => $daysBeforeArrival,
                'refundPercentage' => $refundPercentage,
                'refundAmount' => $refundAmount,
            ],
        );

        $this->addFlash('success', "Votre réservation a été annulée. Remboursement : $refundPercentage%");

        return $this->redirectToRoute('app_home');
    }
     


    /**
    * Fonction de modification d'adresse mail
    */
    #[Route('gite-compte/{id}/modifier-email', name: 'user_update_email')]
    private function updateEmail(User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        // Créer et traiter le formulaire
        $form = $this->createForm(EmailType::class, ['email' => $user->getEmail()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newEmail = $data['email'];

            // Vérifier si l'email a changé
            if ($user->getEmail() !== $newEmail) {
                $user->setEmail($newEmail);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Votre adresse email a été mise à jour.');
                return $this->redirectToRoute('user_profile');
            } else {
                $this->addFlash('info', 'Aucune modification détectée.');
            }
        }
        return $this->redirectToRoute('app_profil_account');

    }
}

