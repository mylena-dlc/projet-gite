<?php

namespace App\Controller;

use App\Form\ContactType;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Mime\Email;
use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', defaults: ['_public_access' => true])]
    public function index(ReservationRepository $reservationRepository,
     PictureRepository $pictureRepository, CategoryRepository $categoryRepository, ReviewRepository $reviewRepository): Response
    {
        $description = 'Découvrez notre gîte à Orbey, en Alsace, idéal pour 4 personnes. Séjournez en pleine nature, avec une vue panoramique sur les Vosges et à proximité du Lac Blanc. Réservez dès maintenant !';

        // Recherche des images de couverture
        $categories = $categoryRepository->findAll();
        $picturesByCategory = [];

        foreach ($categories as $category) {
            $cover = $pictureRepository->findOneBy(['category' => $category, 'is_cover' => true]);
            if ($cover) {
                $picturesByCategory[$category->getName()] = $cover;
            }
        }

        // Recherche des avis
        $reviews = $reviewRepository->findAll();

        return $this->render('home/index.html.twig', [
            'description' => $description,
            'categories' => $categories,
            'picturesByCategory' => $picturesByCategory,
            'reviews' => $reviews
        ]);
    }

    /**
    * Fonction de recherche de réservation
    */
    #[Route('/recherche-reservation', name: 'app_search_reservation', defaults: ['_public_access' => true])]
    public function reservation(ReservationRepository $reservationRepository, FactoryInterface $factory, SluggerInterface $slugger): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Recherche de réservation'); 
        $slug = $slugger->slug('recherche-reservation');

        $description = 'Vérifiez les disponibilités et réservez votre séjour dans notre gîte à Orbey. Profitez d\'un cadre naturel exceptionnel et d\'un hébergement tout confort.';

        // Affichage des dates déjà réservées
        $reservations = $reservationRepository->findReservationsWithStatuses(['confirmée', 'en attente']);

        $datas = [];

        foreach($reservations as $reservation) {
            $datas[] = [
                'id' => $reservation->getId(),
                'start' => $reservation->getArrivalDate()->format('Y-m-d'),
                'end' => $reservation->getDepartureDate()->format('Y-m-d'),
                'color' => '#adadad', 
                'rendering' => 'background' 
            ];
        }
        $data = json_encode($datas); 

        return $this->render('reservation/search.html.twig', [
            'description' => $description,
            'reservedDates' => $data,
            'breadcrumb' => $breadcrumb,
            'slug' => $slug,
        ]);
    }


    /**
    * Fonction de redirection vers la galerie
    */
    #[Route('/galerie', name: 'app_galery', defaults: ['_public_access' => true])]
    public function showGalerie(CategoryRepository $categoryRepository, FactoryInterface $factory): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Galerie'); 


        $categories = $categoryRepository->findAll();

        $description = "Parcourez les photos du gîte en Alsace : 
        séjour cosy, chambres confortables, vue sur la montagne et cadre naturel exceptionnel à Orbey";

        return $this->render('home/galery.html.twig', [
            'description' => $description,
            'categories' => $categories,
            'breadcrumb' => $breadcrumb
        ]);    
    }


    /**
    * Fonction de redirection vers les mentions légales
    */
    #[Route('/mentions-legales', name: 'app_mentions_legales', defaults: ['_public_access' => true])]
    public function mentionsLegales(): Response
    {
        return $this->render('home/mentions-legales.html.twig');
    }


    /**
    * Fonction de redirection vers la politique de confidentialité
    */
    #[Route('/politique-de-confidentialite', name: 'app_politique_confidentialite', defaults: ['_public_access' => true])]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('home/politique-confidentialite.html.twig');
    }


    /**
    * Fonction de redirection vers les conditions générales de vente
    */

    #[Route('/conditions-generales-de-vente', name: 'app_condition_generales_vente')]
    public function conditionsGeneralesVente(): Response
    {
        return $this->render('home/cgv.html.twig');
    }


    /**
    * Fonction pour afficher la page contact
    */
    #[Route('/contact', name: 'app_contact', defaults: ['_public_access' => true])]
    public function pageContact(FactoryInterface $factory, MailerInterface $mailer, Request $request, FormFactoryInterface $formFactory): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Contact'); 

        $description = 'Besoin d’informations ? Contactez-nous pour toute question sur la réservation, l’accès ou les services de notre gîte en Alsace.';
        
        $form = $formFactory->create(ContactType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        
            $email = (new Email())
            ->from($data['email'])
            ->to('contact@gite-rain-du-pair.fr')
            ->subject($data['subject'])
            ->html($data['message']);

        try {
            $mailer->send($email);
            $this->addFlash('success', 'Votre message a été envoyé avec succès.');
            return $this->redirectToRoute('app_contact');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
        }
    }

        return $this->render('home/contact.html.twig', [
            'description' => $description,
            'breadcrumb' => $breadcrumb,
            'contactForm' => $form->createView()
        ]);
    }
    

    /**
    * Fonction pour afficher la page FAQ
    */
    #[Route('/foire-aux-questions', name: 'app_faq', defaults: ['_public_access' => true])]
    public function pageFAQ(FactoryInterface $factory): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('FAQ'); 

        $description = 'Retrouvez ici les réponses aux questions fréquemment posées sur notre gîte en Alsace : réservation, équipements, tarifs et bien plus. Simplifiez votre séjour en consultant notre FAQ détaillée.';
        
        return $this->render('home/faq.html.twig', [
            'description' => $description,
            'breadcrumb' => $breadcrumb
        ]);
    }
}
