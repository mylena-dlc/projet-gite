<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Reservation;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use App\Form\ReservationType;
use App\Service\DompdfService;
use Knp\Menu\FactoryInterface;
use App\Entity\ReservationExtra;
use App\Service\LocationService;
use App\Service\SendEmailService;
use App\Form\ReservationExtraType;
use App\Repository\GiteRepository;
use App\Repository\UserRepository;
use App\Repository\ExtraRepository;
use App\Repository\TokenRepository;
use App\Service\PhoneNumberService;
use App\Service\ReservationService;
use App\Repository\PeriodRepository;
use App\Service\StripePaymentService;
use App\Service\StripePaiementService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    /**
     * @var ReservationRepository
     */
    private $reservationRepository;

    /**
     * @var GiteRepository
     */
    private $giteRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PeriodRepository
     */
    private $periodRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private ReservationService $reservationService;

    
    public function __construct(ReservationRepository $reservationRepository, GiteRepository $giteRepository, EntityManagerInterface $em, UserRepository $userRepository, PeriodRepository $periodRepository, ReservationService $reservationService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->giteRepository = $giteRepository;
        $this->userRepository = $userRepository;
        $this->periodRepository = $periodRepository;
        $this->em = $em;
        $this->reservationService = $reservationService;
    }

        #[Route('/reservation', name: 'app_reservation', defaults: ['_public_access' => true])]
        public function index(Request $request, FactoryInterface $factory): Response
        {
            // Créez un menu "breadcrumb"
            $breadcrumb = $factory->createItem('root');
            $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
            $breadcrumb->addChild('Demande de réservation');  
            $description = 'Planifiez votre séjour dans notre gîte de charme à Orbey en Alsace. Consultez les disponibilités, tarifs, et réservez vos dates en quelques clics. Profitez d\'une escapade inoubliable !';

            // Initialisation des variables avec des valeurs par défaut
            $startDate = null;
            $endDate = null;
            $numberAdult = null;
            $numberKid = null; 
            $totalNight = 0;
            $nightPrice = 0;
            $cleaningCharge = 0;
            $supplement = 0;
            $totalPrice = 0;
            $price = 0;
            $tva = 0;
            $tax = 0;
            $extraForm = null;

            // Récupération des données de session si elles existent
            $session = $request->getSession();
    
            $reservationDetails = $session->get('reservation_details');
            // Si des données de session existent, on initialise les variables
            if ($reservationDetails !== null) {
                $startDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']);
                $endDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']);
                $numberAdult = $reservationDetails['numberAdult'];
                $numberKid = $reservationDetails['numberKid'];
                $totalNight = $reservationDetails['totalNight'];
                $nightPrice = $reservationDetails['nightPrice'];
                $cleaningCharge = $reservationDetails['cleaningCharge'];
                $supplement = $reservationDetails['supplement'];
                $totalPrice = $reservationDetails['totalPrice'];
                $price = $reservationDetails['price'];
                $tva = $reservationDetails['tva'];
                $tax = $reservationDetails['tax'];
            }
    
            if ($request->isMethod('POST')) {
                // Récupérez les données du formulaire
                $startDateInput = $request->get('startDate');
                $endDateInput = $request->get('endDate');
                $numberAdult = intval($request->get('numberAdult')); // Convertir en entier, valeur par défaut 0 si c'est vide 
                $numberKid = intval($request->get('numberKid'));
    
                // Vérifiez si les dates sont fournies
                if (!$startDateInput || !$endDateInput) {
                    $this->addFlash('error', 'Vous devez sélectionner vos dates.');
                    return $this->redirectToRoute('app_home');
                }
    
                // Convertissez les chaînes en objets DateTime
                $dateFormat = 'd/m/Y';
                $startDate = \DateTime::createFromFormat($dateFormat, $startDateInput);
                $endDate = \DateTime::createFromFormat($dateFormat, $endDateInput);
    
                // Vérifier si la conversion a réussi
                if (!$startDate || !$endDate) {
                    $this->addFlash('error', 'Format de date invalide.');
                    return $this->redirectToRoute('app_home');
                }
    
                // Vérifiez si les dates ne chevauchent pas des réservations existantes
                $overlappingReservations = $this->reservationRepository->findOverlappingReservations($startDate, $endDate);
                if (!empty($overlappingReservations)) {
                    $this->addFlash('error', 'Les dates choisies ne sont plus disponibles.');
                    return $this->redirectToRoute('app_home');
                }

                // Récupération du gîte
                $gite = $this->giteRepository->find(1);
                $nightPrice = $gite->getPrice();
                $cleaningCharge = $gite->getCleaningCharge();

                try {
                    // Appel des méthodes du service séparément
                    $totalNight = $this->reservationService->calculateTotalNights($startDate, $endDate);
                    $supplement = $this->reservationService->calculateSupplement($startDate, $endDate);
                    $basePrice = $this->reservationService->calculateBasePrice($totalNight, $nightPrice, $cleaningCharge, $supplement);
                    $tax = $this->reservationService->calculateTax($basePrice, $totalNight, $numberAdult, $numberKid);
                    $tva = $this->reservationService->calculateTva($basePrice);
                    $totalPrice = $this->reservationService->calculateTotalPrice($basePrice, $tva, $tax);

                    // Stockage dans la session 
                    $session->set('reservation_details', [
                        'startDate' => $startDate->format($dateFormat),
                        'endDate' => $endDate->format($dateFormat),
                        'numberAdult' => $numberAdult,
                        'numberKid' => $numberKid,
                        'totalNight' => $totalNight,
                        'nightPrice' => $nightPrice,
                        'cleaningCharge' => $cleaningCharge,
                        'supplement' => $supplement,
                        'price' => $gite->getPrice() * $totalNight,
                        'tva' => $tva,
                        'tax' => $tax,
                        'totalPrice' => $totalPrice,
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('app_home');
                }
            }            
            return $this->render('reservation/index.html.twig', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'numberAdult' => $numberAdult,
                'numberKid' => $numberKid,
                'totalNight' => $totalNight,
                'nightPrice' => $nightPrice,
                'cleaningCharge' => $cleaningCharge,
                'supplement' => $supplement,
                'tva' => $tva,
                'tax' => $tax,
                'totalPrice' => $totalPrice,
                'description' => $description,
                'breadcrumb' => $breadcrumb,
                'extraForm' => $extraForm ? $extraForm->createView() : null, 
            ]);
        }


    /**
     * Fonction pour ajouter un extra (limité à un seul pour la durée du séjour)
     */
    #[Route('/reservation/ajouter-option', name: 'add_reservation_extra', methods: ['POST'])]
    public function addExtra(Request $request, ExtraRepository $extraRepository, SessionInterface $session, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['date'])) {
            return new JsonResponse(['success' => false, 'message' => 'Date invalide.']);
        }

        // Récupération des détails du séjour
        $reservationDetails = $session->get('reservation_details', []);
        $logger->info('DEBUG | Données session réservation', $reservationDetails);

        if (!isset($reservationDetails['startDate']) || !isset($reservationDetails['endDate'])) {
            return new JsonResponse(['success' => false, 'message' => 'Les dates du séjour sont introuvables.']);
        }

        // Conversion des dates
        $arrival = \DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']);
        $departure = \DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']);
        $selectedDate = \DateTime::createFromFormat('Y-m-d', $data['date']);

        if (!$arrival || !$departure || !$selectedDate) {
            $logger->error('Erreur lors du parsing des dates', [
                'arrival_raw' => $reservationDetails['startDate'],
                'departure_raw' => $reservationDetails['endDate'],
                'selected_raw' => $data['date'],
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la conversion des dates.'
            ]);
        }

        // Vérification que la date est dans la plage du séjour
        if ($selectedDate < $arrival || $selectedDate > $departure) {
            return new JsonResponse([
                'success' => false,
                'message' => 'La date sélectionnée doit être comprise dans la durée du séjour.'
            ]);
        }

        // Récupération de l'extra par défaut 
        $extra = $extraRepository->find(2);



// Vérifie si cet extra spécifique est déjà présent
$reservationExtras = $session->get('reservation_extras', []);

        $logger->info('DEBUG | Contenu de reservation_extras', $reservationExtras);

foreach ($reservationExtras as $existingExtra) {
    if ($existingExtra['extra_id'] == $extra->getId()) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Un accès au bain nordique est déjà ajouté pour ce séjour.'
        ]);
    }
}
        // Ajout de l'extra
        $reservationExtras[] = [
            'extra_id' => $extra->getId(),
            'extraName' => $extra->getName(),
            'price' => $extra->getPrice(),
            'date' => $data['date'],
        ];

        $session->set('reservation_extras', $reservationExtras);

        $totalExtraPrice = array_sum(array_column($reservationExtras, 'price'));
        $totalPrice = $reservationDetails['totalPrice'];

        return new JsonResponse([
            'success' => true,
            'message' => 'L\'extra a été ajouté avec succès.',
            'reservationExtras' => $reservationExtras,
            'totalExtraPrice' => $totalExtraPrice,
            'newTotalPrice' => $totalPrice + $totalExtraPrice,
        ]);
    }



    /**
    * Fonction pour supprimer un extra
    */
    #[Route('/reservation/supprimer-option', name: 'remove_reservation_extra', methods: ['POST'])]
    public function removeExtra(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $extraId = $data['extra_id'] ?? null;

        $reservationExtras = $session->get('reservation_extras', []);
        $reservationExtras = array_filter($reservationExtras, function ($extra) use ($extraId) {
            return $extra['extra_id'] != $extraId;
        });
    
        $session->set('reservation_extras', $reservationExtras);
    
        $totalExtraPrice = array_sum(array_column($reservationExtras, 'price'));
    
        return new JsonResponse([
            'success' => true,
            'message' => 'L\'extra a été supprimé avec succès.',
            'totalExtraPrice' => $totalExtraPrice,
            'newTotalPrice' => $session->get('reservation_details')['totalPrice'] + $totalExtraPrice,
        ]);
    }

    
    #[Route('/reservation/demande', name: 'new_reservation')]
    public function new(Request $request, Security $security, FactoryInterface $factory, 
        TokenRepository $tokenRepository, ExtraRepository $extraRepository,
         SessionInterface $session,
         GiteRepository $giteRepository,
         PhoneNumberService $phoneNumberService,
         StripePaymentService $stripePaymentService,
         EntityManagerInterface $em,
         LocationService $locationService): Response 
    {
        if (!$security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $session->set('_security.main.target_path', $request->getUri()); // Sauvegarde la page actuelle
            $this->addFlash('error', 'Vous devez vous connecter ou créer un compte pour continuer votre réservation.');
            return $this->redirectToRoute('app_login');
        }
        
        // Créer un menu "breadcrumb"
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Demande de réservation', ['route' => 'app_reservation']);
        $breadcrumb->addChild('Confirmation de réservation');  
    
        // Récupérer les données stockées en session
        $reservationDetails = $session->get('reservation_details', []);
    
        if (empty($reservationDetails)) {
            $this->addFlash('error', 'Aucune réservation en cours.');
            return $this->redirectToRoute('app_reservation');
        }
    
        // Extraire les informations pour l'affichage dans la vue
        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['startDate']);
        $departureDate = \DateTime::createFromFormat('d/m/Y', $reservationDetails['endDate']);
        $numberAdult = $reservationDetails['numberAdult'];
        $numberKid = $reservationDetails['numberKid'];
        $totalNight = $reservationDetails['totalNight'];
        $nightPrice = $reservationDetails['nightPrice'];
        $cleaningCharge = $reservationDetails['cleaningCharge'];
        $supplement = $reservationDetails['supplement'];
        $tva = $reservationDetails['tva'];
        $tax = $reservationDetails['tax'];
        $totalPrice = $reservationDetails['totalPrice'];
    
        // Gérer les éventuels extras en session
        $reservationExtras = $session->get('reservation_extras', []);
        $totalExtraPrice = array_sum(array_column($reservationExtras, 'price'));
    
        // Formulaire d'ajout d'extras
        $reservationExtra = new ReservationExtra();
        $extraForm = $this->createForm(ReservationExtraType::class, $reservationExtra);

        // Formulaire de réservation
        $reservation = new Reservation();

        $reservationForm = $this->createForm(ReservationType::class, $reservation);

        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            // Vérification et formatage du numéro de téléphone
            $phone = $reservation->getPhone();
            $country = $reservation->getCountry();
            $formattedPhone = $phoneNumberService->formatPhoneNumber($phone, $country);
    
            if ($formattedPhone === null) {
                $this->addFlash('error', "Le numéro de téléphone fourni est invalide. Veuillez entrer un numéro valide.");
                return $this->redirectToRoute('new_reservation');
            }
            $reservation->setPhone($formattedPhone);
           
            // exit;
            /** @var User $user */
            $user = $this->getUser();

            // Stocker les détails en session pour Stripe
            $reservationDetails['phone'] = $formattedPhone;
            $reservationDetails['email'] = $user->getEmail();
            $reservationDetails['userId'] = $user->getId();
            $reservationDetails['giteId'] = $giteRepository->find(1)->getId();
            $reservationDetails['giteName'] = $giteRepository->find(1)->getName();

            $reservationDetails['lastName'] = $reservation->getLastName();
            $reservationDetails['firstName'] = $reservation->getFirstName();
            $reservationDetails['address'] = $reservation->getAddress();
            $reservationDetails['city'] = $reservation->getCity();
            $reservationDetails['cp'] = $reservation->getCp();
            $reservationDetails['country'] = $reservation->getCountry();
            $reservationDetails['isMajor'] = $reservation->getIsMajor();
            $reservationDetails['message'] = $reservation->getMessage();

            $session->set('reservation_details', $reservationDetails);

            $reservationDetails['arrival_date'] = $reservation->getarrivalDate();
            $reservationDetails['departure_date'] = $reservation->getdepartureDate();
            $reservationDetails['number_adult'] = $reservation->getnumberAdult();
            $reservationDetails['number_kid'] = $reservation->getnumberKid();
            
            // Si un token a été renseigné, récupérer ses données
            if ($session->has('reservation_token')) {
                $promoCode = $session->get('reservation_token')['promoCode'];
                $discount = $session->get('reservation_token')['discount'];
                $newTotalPrice = $session->get('reservation_token')['newTotalPrice'];
            } else {
                $promoCode = null;
                $discount = 0;
                $newTotalPrice = null;
                $reservationToken = [];
            }

            // On récupère le token (code promo)
            if(!$promoCode == null) {
                $token = $tokenRepository->findOneBy(['code' => $promoCode]);
                $reservation->setToken($token); 
            }

            // Créer la session de paiement Stripe et récupérer l'URL
            $paymentUrl = $stripePaymentService->createPaymentSession($reservationDetails);

            // Rediriger directement vers Stripe
             return $this->redirect($paymentUrl);

            $reservation->setNumberAdult($reservationDetails['numberAdult']);
            $reservation->setNumberKid($reservationDetails['numberKid']);
            $reservation->setTotalNight($reservationDetails['totalNight']);
            $reservation->setPriceNight($reservationDetails['nightPrice']);
            $reservation->setCleaningCharge($reservationDetails['cleaningCharge']);
            $reservation->setSupplement($reservationDetails['supplement']);
            $reservation->setTva($reservationDetails['tva']);
            $reservation->setTourismTax($reservationDetails['tax']);
            $reservation->setTotalPrice($reservationDetails['totalPrice']);
            $reservation->setLastName($reservationDetails['lastName']);
            $reservation->setFirstName($reservationDetails['firstName']);
            $reservation->setaddress($reservationDetails['address']);
            $reservation->setCp($reservationDetails['cp']);
            $reservation->setCity($reservationDetails['city']);
            $reservation->setCountry($reservationDetails['country']);
            $reservation->setPhone($reservationDetails['phone']);
            $reservation->setEmail($reservationDetails['email']);
            $reservation->setIsMajor($reservationDetails['isMajor']);
            $reservation->setMessage($reservationDetails['message'] ?? '');

            $reservation->setArrivalDate($arrivalDate);
            $reservation->setDepartureDate($departureDate);

            $description = 'Validez votre réservation pour notre gîte à Orbey. Vérifiez les détails, les tarifs, et complétez vos coordonnées en toute sécurité. Séjournez dans notre charmant hébergement en Alsace.';

            return $this->redirectToRoute('confirm_reservation', [
                'slug' => $reservation->getSlug(),
            ]);
        
        }
    
        $description = 'Validez votre réservation pour notre gîte à Orbey. Vérifiez les détails, les tarifs, et complétez vos coordonnées en toute sécurité. Séjournez dans notre charmant hébergement en Alsace.';
    
        return $this->render('reservation/new.html.twig', [
            'arrivalDate' => $arrivalDate->format('d-m-Y'),
            'departureDate' => $departureDate->format('d-m-Y'),
            'numberAdult' => $numberAdult,
            'numberKid' => $numberKid,
            'totalNight' => $totalNight,
            'nightPrice' => $nightPrice,
            'cleaningCharge' => $cleaningCharge,
            'supplement' => $supplement,
            'tva' => $tva,
            'tax' => $tax,
            'totalPrice' => $totalPrice,
            'description' => $description,
            'breadcrumb' => $breadcrumb,
            'promoCode' => 0,
            'newTotalPrice' => 0,
            'discount' => 0,
            'extraForm' => $extraForm->createView(),
            'form' => $reservationForm->createView(),
            'reservationExtras' => $reservationExtras,
            'totalExtraPrice' => $totalExtraPrice,
        ]);
    }
    

    #[Route('/reservation/confirmation', name: 'reservation_confirm', methods: ['GET'])]
    public function confirm(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');
    
        if (!$sessionId) {
            return new Response('Erreur : Aucun identifiant de session Stripe fourni.', 400);
        }
    
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    
        try {
            $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);
    
            if ($stripeSession->payment_status !== 'paid') {
                return new Response('Paiement non confirmé.', 400);
            }
    
            $paymentIntentId = $stripeSession->payment_intent;
        } catch (\Exception $e) {
            return new Response('Une erreur est survenue lors du paiement.', 400);
        }
    
        // Attente douce que le webhook ait créé la réservation
        $reservation = null;
        $timeout = 3; // max 3 secondes
        $elapsed = 0;
    
        while (!$reservation && $elapsed < $timeout) {
            usleep(300000); // 300 ms
            $reservation = $em->getRepository(Reservation::class)->findOneBy([
                'stripe_payment_id' => $paymentIntentId
            ]);
            $elapsed += 0.3;
        }
    
        if (!$reservation) {
            return new Response('Erreur : Réservation introuvable.', 404);
        }
    
        $description = 'Votre réservation dans notre gîte de charme à Orbey en Alsace est confirmée. Préparez-vous à vivre une expérience exceptionnelle dans notre maison de vacances!';
    
        return $this->render('reservation/confirm.html.twig', [
            'reservation' => $reservation,
            'description' => $description
        ]);
    }
    


    /**
    * Fonction pour afficher une page d'erreur si le paiement échoue
    */
    #[Route('/reservation/error', name: 'payment_error')]
    public function stripeError(SessionInterface $session)
    {
        // Supprime les données de réservation côté client
        $session->remove('reservation_details');

        $this->addFlash('error', 'Le paiement a été annulé ou a échoué. Veuillez réessayer votre réservation.');
        return $this->redirectToRoute('app_home');
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
    * Fonction pour vérifier un code promo
    */
    #[Route('/verification-code-promo', name: 'check_token', methods: ['POST'])]
    public function checkToken(Request $request, TokenRepository $tokenRepository): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Requête invalide.'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $promoCode = $data['code'] ?? '';

        if (empty($promoCode)) {
            return new JsonResponse(['success' => false, 'message' => 'Le code promo est requis.'], 400);
        }

        // Récupérer les tokens actifs
        $activeTokens = $tokenRepository->findActiveTokens();

        // Chercher un token avec le code correspondant
        $matchedToken = null;
        foreach ($activeTokens as $token) {
            if (strtolower($token->getCode()) === strtolower($promoCode)) {
                $matchedToken = $token;
                break;
            }
        }

        if (!$matchedToken) {
            return new JsonResponse(['success' => false, 'message' => 'Code promo invalide ou expiré.'], 404);
        }

        // Récupération du prix total en session
        $session = $request->getSession();
        $reservationDetailsTokenToken = $session->get('reservation_token', []); // Retourne un tableau vide si aucune donnée

        // Recherche du prix actuel en Session
        $totalPrice = $session->get('reservation_details')['totalPrice'];

        // Calculer le nouveau total avec la réduction
        $discount = $matchedToken->getDiscount();
        $newTotalPrice = $totalPrice - ($totalPrice * ($discount / 100));

        $reservationDetailsToken['promoCode'] = $promoCode;
        $reservationDetailsToken['discount'] = $discount;
        $reservationDetailsToken['newTotalPrice'] = number_format($newTotalPrice, 2);
        
        // Sauvegarder les données fusionnées dans la session
        $session->set('reservation_token', $reservationDetailsToken);

        return new JsonResponse([
            'success' => true,
            'newTotalPrice' => number_format($newTotalPrice, 2),
            'discount' => $discount,
            'totalPrice' => $totalPrice
        ]);
    }
}




