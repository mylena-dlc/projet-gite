<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Extra;
use App\Entity\Period;
use App\Entity\Review;
use App\Entity\Picture;
use App\Form\ExtraType;
use App\Entity\Category;
use App\Form\PeriodType;
use App\Form\ReviewType;
use App\Form\PictureType;
use App\Form\CategoryType;
use App\Form\PictureCoverType;
use App\Service\DompdfService;
use App\Service\RefundService;
use App\Service\SendEmailService;
use App\Repository\GiteRepository;
use App\Repository\UserRepository;
use App\Repository\ExtraRepository;
use App\Repository\PeriodRepository;
use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use App\Repository\CategoryRepository;
use App\Service\SmsNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ReservationExtraRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DashboardController extends AbstractController
{
    /**
    * Fonction pour afficher toutes les réservation de l'accueil
    */
    #[Route('private-zone-224/dashboard', name: 'admin_dashboard')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // Recherche s'il y a une réservation à venir 
        $ongoingReservation = $reservationRepository->findOngoingReservation(); 

        // Recherche toutes les réservations à venir
        $upcomingReservations = $reservationRepository->findUpcomingReservations();

        // Recherche toutes les réservations passées
        $previousReservations = $reservationRepository->findPreviousReservations();

        // Recherche toutes les réservations à confirmer par l'admin
        $reservationsToConfirms = $reservationRepository->findReservationsToConfirm();

        // Recherche toutes les réservations refusées par l'admin
        $reservationsRefuseds = $reservationRepository->findReservationsRefused();

        // Recherche toutes les réservations annulées
        $reservationsCancelled = $reservationRepository->findReservationsCancel();

         // Déterminer la catégorie à afficher par défaut
        $defaultCategory = null;
        if (!empty($reservationsToConfirms)) {
            $defaultCategory = 'confirm';
        } elseif (!empty($ongoingReservation)) {
            $defaultCategory = 'ongoing';
        } elseif (!empty($upcomingReservations)) {
            $defaultCategory = 'upcoming';
        }

        return $this->render('admin/index.html.twig', [
            'upcomingReservations' => $upcomingReservations,
            'previousReservations' => $previousReservations,
            'ongoingReservation' => $ongoingReservation,
            'reservationsToConfirms' => $reservationsToConfirms,
            'reservationsRefuseds' => $reservationsRefuseds,
            'reservationsCancelled' => $reservationsCancelled,
            'defaultCategory' => $defaultCategory,
        ]);
    }


    /**
    * Fonction pour afficher les détails d'une réservation
    */
    #[Route('private-zone-224/dashboard/reservation/{slug}', name: 'admin_reservation')]
    #[Route('private-zone-224/dashboard/reservation/{slug}/export', name: 'admin_reservation_export')]
    public function showReservation(ReservationRepository $reservationRepository, string $slug, DompdfService $dompdfService,  Request $request): Response
    {
        // Récupérez la réservation 
        $reservation = $reservationRepository->findOneBy(['slug' => $slug]);
        $gite = $reservation->getGite();

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

            // Créez une réponse de téléchargement
            $reference = preg_replace('/[^a-zA-Z0-9_-]/', '', $reservation->getReference());
            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename=FACTURE-' . $reference . '.pdf');
            return $response;
        }

        return $this->render('admin/show_reservation.html.twig', [
            'reservation' => $reservation
        ]);
    }


    /**
    * Fonction pour afficher la page du menu
    */
    #[Route('private-zone-224/dashboard/menu', name: 'admin_menu')]
    public function showMenu(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findBy(['is_verified' =>0], ['creation_date' => 'DESC']);

        return $this->render('admin/menu.html.twig', [
            'reviews' => $reviews
        ]);
    }


    // /**
    // * Fonction pour afficher la page revenus
    // */
    // #[Route('private-zone-224/dashboard/income', name: 'admin_income')]
    // public function showIcome(ReservationRepository $reservationRepository): Response
    // {
    //     // Détermine le mois et l'année en cours
    //     $currentDate = new \DateTime();
    //     $currentMonth = (int)$currentDate->format('m');  // Récupère le mois courant (1 à 12)
    //     $currentYear = (int)$currentDate->format('Y');   // Récupère l'année courante

    //     // Récupère toutes les réservations du mois en cours
    //     $reservationsForCurrentMonth = $reservationRepository->findReservationsByMonth($currentMonth, $currentYear);

    //     // Génère les noms des mois
    //     $monthsLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        
    //     // Génère un tableau associatif avec les mois et leurs revenus
    //     $monthlyIncomes = [];
    //     $years = [$currentYear - 1, $currentYear]; // Inclu l'année précédente et actuelle
    //     foreach ($years as $year) {
    //         for ($month = 1; $month <= 12; $month++) {
    //             $reservations = $reservationRepository->findReservationsByMonth($month, $year);
    //             $monthlyRevenue = array_sum(array_map(function($reservation) {
    //                 return $reservation->getTotalPrice() 
    //                     - $reservation->getTva() 
    //                     - $reservation->getTourismTax();
    //             }, $reservations));
        
    //             $monthlyIncomes[] = [
    //                 'month' => $monthsLabels[$month - 1],
    //                 'year' => $year,
    //                 'income' => $monthlyRevenue,
    //                 'link' => $this->generateUrl('admin_report', [
    //                     'month' => $month,
    //                     'year' => $year
    //                 ])
    //             ];
    //         }
    //     }

    //     // Recherche de toutes les réservations
    //     $transactions = [];
    //     $allReservations = $reservationRepository->findBy([], ['reservation_date' => 'DESC']);

    //      // Stocker chaque réservation avec la date et le montant total
    //     foreach ($allReservations as $reservation) {
    //         $transactions[] = [
    //             'date' => $reservation->getReservationDate(),
    //             'totalPrice' => $reservation->getTotalPrice(),
    //         ];
    //     }

    //     $graphData = [];
        
    //     foreach ($monthlyIncomes as $item) {
    //         $graphData[$item['month'] . ' ' . $item['year']] = $item['income'];
    //     }

    //     return $this->render('admin/income.html.twig', [
    //         'reservationsForCurrentMonth' => $reservationsForCurrentMonth,
    //         'labels' => $monthsLabels,
    //         'monthlyIncomes' => $monthlyIncomes,
    //         'transactions' => $transactions,
    //         'years' => $years,
    //         'currentYear' => $currentYear,
    //         'graphData' => $graphData,
    //     ]);
    // }


    #[Route('private-zone-224/dashboard/income', name: 'admin_income')]
public function showIncome(ReservationRepository $reservationRepository): Response
{
    $currentDate = new \DateTime();
    $currentMonth = (int)$currentDate->format('m');
    $currentYear = (int)$currentDate->format('Y');

    // ⚙️ Mois formatés
    $monthsLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    // ✅ Années à afficher dans les graphes (ex: année actuelle et précédente)
    $years = [$currentYear - 1, $currentYear];

    // 💰 Structure par année pour le carousel graphique
    $graphDataByYear = [];

    foreach ($years as $year) {
        $graphDataByYear[$year] = [];

        for ($month = 1; $month <= 12; $month++) {
            $reservations = $reservationRepository->findReservationsByMonth($month, $year);
            $monthlyRevenue = array_sum(array_map(function ($reservation) {
                return $reservation->getTotalPrice()
                    - $reservation->getTva()
                    - $reservation->getTourismTax();
            }, $reservations));

            $monthLabel = $monthsLabels[$month - 1];
            $graphDataByYear[$year][$monthLabel] = $monthlyRevenue;
        }
    }

    // 📊 Revenus actuels pour le mois en cours
    $reservationsForCurrentMonth = $reservationRepository->findReservationsByMonth($currentMonth, $currentYear);

    // 📄 Bloc "Rapport de revenus" cliquables
    $monthlyIncomes = [];
    foreach ($years as $year) {
        for ($month = 1; $month <= 12; $month++) {
            $reservations = $reservationRepository->findReservationsByMonth($month, $year);
            $monthlyRevenue = array_sum(array_map(function ($reservation) {
                return $reservation->getTotalPrice()
                    - $reservation->getTva()
                    - $reservation->getTourismTax();
            }, $reservations));

            $monthlyIncomes[] = [
                'month' => $monthsLabels[$month - 1],
                'year' => $year,
                'income' => $monthlyRevenue,
                'link' => $this->generateUrl('admin_report', [
                    'month' => $month,
                    'year' => $year
                ])
            ];
        }
    }

    // 💳 Liste de toutes les transactions
    $transactions = [];
    $allReservations = $reservationRepository->findBy([], ['reservation_date' => 'DESC']);
    foreach ($allReservations as $reservation) {
        $transactions[] = [
            'date' => $reservation->getReservationDate(),
            'totalPrice' => $reservation->getTotalPrice(),
        ];
    }

    return $this->render('admin/income.html.twig', [
        'reservationsForCurrentMonth' => $reservationsForCurrentMonth,
        'monthlyIncomes' => $monthlyIncomes,
        'transactions' => $transactions,
        'graphDataByYear' => $graphDataByYear,
        'years' => $years,
        'currentYear' => $currentYear,
    ]);
}



    /**
    * Fonction pour afficher la page revenus par mois + l'export en PDF
    */
    #[Route('private-zone-224/dashboard/report/{month}/{year}', name: 'admin_report')]
    #[Route('private-zone-224/dashboard/report/{month}/{year}/export', name: 'admin_report_export')]
    public function showReport(ReservationRepository $reservationRepository, int $month, int $year, DompdfService $dompdfService, Request $request): Response
    {
        // Tableau des noms de mois
        $monthNames = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre'
        ];

        // S'assurer que $month est un entier
        $month = (int)$month; // Convertir le mois en entier si ce n'est pas déjà fait
        $monthName = $monthNames[$month] ?? 'Mois inconnu'; // Récupérer le nom du mois ou une valeur par défaut

        // Récupère toutes les réservations pour le mois et l'année donnés
        $reservations = $reservationRepository->findReservationsByMonth($month, $year);

        // Calcul du nombre total de nuits
        $totalNights = array_sum(array_map(function($reservation) {
            $checkIn = $reservation->getArrivalDate(); 
            $checkOut = $reservation->getDepartureDate();
            return $checkOut->diff($checkIn)->days;
        }, $reservations));

        // Calcul des revenus
        $totalHT = array_sum(array_map(function($reservation) {
            return $reservation->getTotalPrice() - $reservation->getTva() - $reservation->getTourismTax();
        }, $reservations));

        $totalTTC = array_sum(array_map(function($reservation) {
            return $reservation->getTotalPrice();
        }, $reservations));

        $totalTVA = array_sum(array_map(function($reservation) {
            return $reservation->getTva();
        }, $reservations));

        $totalTourismTax = array_sum(array_map(function($reservation) {
            return $reservation->getTourismTax();
        }, $reservations));

        // Calcul de la durée moyenne des séjours
        $totalReservations = count($reservations); 
        $averageStayDuration =  $totalNights / $totalReservations;

        // Si le paramètre "export" est présent, générer le PDF
        if ($request->attributes->get('_route') === 'admin_report_export') {
            $html = $this->renderView('admin/report_export_pdf.html.twig', [
                'month' => $monthName,
                'year' => $year,
                'reservations' => $reservations,
                'totalReservations' => $totalReservations,
                'totalNights' => $totalNights,
                'totalHT' => $totalHT,
                'totalTTC' => $totalTTC,
                'totalTVA' => $totalTVA,
                'totalTourismTax' => $totalTourismTax,
                'averageStayDuration' => $averageStayDuration,
            ]);

        $pdfContent = $dompdfService->generatePdf($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport_revenus_' . $month . '_' . $year . '.pdf"'
        ]);
    }

        return $this->render('admin/report.html.twig', [
            'month' => $monthName, // Nom du mois
            'monthNumber' => $month, // Numéro du mois
            'year' => $year,
            'reservations' => $reservations,
            'totalNights' => $totalNights,
            'averageStayDuration' => $averageStayDuration,
            'totalHT' => $totalHT,
            'totalTTC' => $totalTTC,
            'totalTVA' => $totalTVA,
            'totalTourismTax' => $totalTourismTax
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


    #[Route('private-zone-224/dashboard/reservation/test/{id}', name: 'admin_test')]
    public function test(ReservationRepository $reservationRepository, int $id, DompdfService $dompdfService,  Request $request): Response
    {
           // Récupérez la réservation depuis la base de données
           $reservation = $reservationRepository->find($id);
           $gite = $reservation->getGite();
   
   
           return $this->render('reservation/test.html.twig', [
               'reservation' => $reservation,
               'gite' => $gite
           ]);
       }



    /**
    * Fonction pour afficher la page "Gîte"
    */
    #[Route('private-zone-224/dashboard/gite', name: 'admin_gite', methods: ['GET', 'POST'])]
    public function showGite(GiteRepository $giteRepository, CategoryRepository $categoryRepository, Request $request, Category $category = null, EntityManagerInterface $em, PictureRepository $pictureRepository ): Response
    {
        // Affichage des données
        $gite = $giteRepository->find(1);

        $categories = $categoryRepository->findAll();

        // Récupérer les images groupées par catégorie
        $picturesByCategory = [];
        foreach ($categories as $category) {
            $picturesByCategory[$category->getName()] = $pictureRepository->findBy(['category' => $category]);
        }

        // Ajout d'une catégorie
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            // Si la requête est AJAX, renvoyer une réponse JSON
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'category' => [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                    ]
                ]);
            }

            // Redirection classique si ce n'est pas une requête AJAX
            $this->addFlash("success", "La catégorie a été ajoutée.");
            return $this->redirectToRoute('admin_gite');
        }

        // Ajout d'une image
        $picture = new Picture();
        $formPicture = $this->createForm(PictureType::class, $picture);

        $formPicture->handleRequest($request);
        if($formPicture->isSubmitted() && $formPicture->isValid()) {

            if ($formPicture->get('category')->getData()) {
                $category = $formPicture->get('category')->getData();
                $picture->setCategory($category);
            }
            if ($formPicture->isSubmitted() && $formPicture->isValid()) {
                $this->addFlash('debug', 'Formulaire soumis et valide');
            } else {
                $this->addFlash('debug', 'Formulaire non soumis ou non valide');
            }
            $pictureFile = $formPicture->get('picture')->getData();

            if ($pictureFile) {

                $newFilename = uniqid().'.'.$pictureFile->guessExtension();

                $newFilePath = $this->getParameter('pictures_directory').'/'.$newFilename;

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );                
                    
                    $picture->setUrl('uploads/' . $newFilename);
                    $picture->setIsCover(false); 
                    $picture->setGite($gite);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                } catch (AccessDeniedException $e) {
                    $this->addFlash('error', 'Accès refusé au répertoire de stockage des images.');
                }
            }

            $em->persist($picture);
            $em->flush(); 

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'picture' => [
                        'id' => $picture->getId(),
                        'url' => $picture->getUrl(),
                        'alt' => $picture->getAlt(),
                        'category' => $picture->getCategory()->getName(),
                    ]
                ]);
            }

            $this->addFlash("success", "L'image a été ajoutée.");
            return $this->redirectToRoute('admin_gite');
        }

        return $this->render('admin/gite.html.twig', [
            'gite' => $gite,
            'categories' => $categories,
            'formAddCategory' => $form->createView(),
            'picturesByCategory' => $picturesByCategory,
            'formPicture' => $formPicture->createView(),
        ]);
    }


    /**
    * Fonction pour supprimer une category
    */
    #[Route('private-zone-224/dashboard/gite/category/{id}', name: 'delete_category')]
    public function deleteCategory(Category $category, EntityManagerInterface $em) {

        $em->remove($category);
        $em->flush();

        $this->addFlash("success", "La catégorie a été suprimée.");
        return $this->redirectToRoute('admin_gite');
    }


        private function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }


    /**
    * Fonction pour modifier les infos de la page "Gîte"
    */
    #[Route('private-zone-224/gite/update', name: 'gite_update', methods: ['POST'])]
    public function updateGite(Request $request, GiteRepository $giteRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['id'], $data['field'], $data['value'])) {
            return new JsonResponse(['success' => false, 'message' => 'Données invalides'], 400);
        }
    
        $gite = $giteRepository->find($data['id']);
        if (!$gite) {
            return new JsonResponse(['success' => false, 'message' => 'Gîte introuvable'], 404);
        }

        $field = $data['field'];
        $value = $data['value'];

        // Convertir snake_case en camelCase pour trouver le setter
        $camelCaseField = $this->snakeToCamel($field);
        $setter = 'set' . ucfirst($camelCaseField);

          if (method_exists($gite, $setter)) {
        $gite->$setter($value);
        $em->persist($gite);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

        return new JsonResponse(['success' => false, 'message' => 'Champ invalide'], 400);
    }


    /**
    * Fonction pour supprimer une photo
    */
    #[Route('private-zone-224/dashboard/gite/picture/{id}', name: 'delete_picture', methods: ['DELETE'])]
    public function deletePicture(Picture $picture, EntityManagerInterface $em, Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }
    
        $em->remove($picture);
        $em->flush();
    
        return new JsonResponse(['success' => true, 'message' => 'Image supprimée']);
    }
    

    /**
    * Fonction pour ajouter une image de couverture
    */
    #[Route('private-zone-224/dashboard/gite/cover/{categoryId}/edit', name: 'admin_gite_edit_cover', methods: ['GET', 'POST'])]
    public function editCover(
        Request $request,
        GiteRepository $giteRepository,
        CategoryRepository $categoryRepository,
        PictureRepository $pictureRepository,
        EntityManagerInterface $em,
        string $categoryId
    ): Response {
        // Récupérer la catégorie
        $category = $categoryRepository->findOneBy(['name' => $categoryId]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }

        // Récupérer l'image de couverture actuelle
        $currentCover = $pictureRepository->findOneBy(['category' => $category, 'is_cover' => true]);

        // Préparer le formulaire pour ajouter une nouvelle couverture
        $newCover = new Picture();
        $formPictureCover = $this->createForm(PictureCoverType::class, $newCover);

        $formPictureCover->handleRequest($request);

        if ($formPictureCover->isSubmitted() && $formPictureCover->isValid()) {
            // Supprimer l'image de couverture actuelle si elle existe
            if ($currentCover) {
                $em->remove($currentCover);
            }

            // Enregistrer la nouvelle image
            $newCover->setCategory($category);
            $newCover->setIsCover(true); // Forcer l'attribut isCover
            $pictureFile = $formPictureCover->get('picture')->getData();

            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $pictureFile->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $newCover->setUrl('uploads/' . $newFilename);
                $newCover->setIsCover(true);  
                $newCover->setCategory($category); 
                $gite = $giteRepository->find(1);
                $newCover->setGite($gite); 
            }

            $em->persist($newCover);
            $em->flush();

            $this->addFlash('success', 'Image de couverture mise à jour.');
            return $this->redirectToRoute('admin_gite');
        }

        return $this->render('admin/edit_cover.html.twig', [
            'form' => $formPictureCover->createView(),
            'currentCover' => $currentCover,
            'category' => $category,
        ]);
    }


    /**
    * Fonction pour afficher la vue de confirmation d'une réservation
    */
    #[Route('private-zone-224/dashboard/reservation/{id}/admin_status', name: 'admin_reservation_status')]
    public function statusReservation(int $id, ReservationRepository $reservationRepository): Response
    {
        // Récupérer la réservation par son ID
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'La réservation demandée n\'existe pas.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/confirm_reservation.html.twig', [
            'reservation' => $reservation
        ]);
    }



    /**
    * Fonction pour valider une réservation
    */
    #[Route('private-zone-224/dashboard/reservation/{id}/admin_confirm', name: 'admin_confirm_reservation')]
    public function confirmReservation(int $id, EntityManagerInterface $em, ReservationRepository $reservationRepository,
     GiteRepository $giteRepository, DompdfService $dompdfService, SendEmailService $mail, SmsNotificationService $sms): Response
    {
        // Récupérer la réservation par son ID
        $reservation = $reservationRepository->find($id);
        // Vérifier le statut actuel
        $isConfirm = $reservation->getIsConfirm();

        if (isset($isConfirm['status']) && $isConfirm['status'] === 'confirmée') {
            $this->addFlash('warning', 'La réservation est déjà confirmée.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Mettre à jour le statut
        $reservation->setIsConfirm(['status' => 'confirmée']);
        $em->persist($reservation);
        $em->flush();

        // Données à afficher dans le mail
        $gite = $giteRepository->findOneBy(['id' => 1]);
        $startDate = $reservation->getArrivalDate();
        $endDate = $reservation->getDepartureDate();
        $totalNight = $reservation->getTotalNight();
        $cleaningCharge = $reservation->getCleaningCharge();
        $riceNight = $reservation->getPriceNight();
        $priceHt = $reservation->getTotalPrice() - $cleaningCharge;

        // Récupérer le contenu du template de la facture
        $invoiceContent = $this->renderView('reservation/invoice.html.twig', [
            'reservation' => $reservation,
            'totalNight' => $totalNight,
            'gite' => $gite,
            'priceHt' => $priceHt,
            'cleaningCharge' => $cleaningCharge,
            'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') 
            . '/public/assets/img/logo-gite-rain-du-pair.png'),
        ]);

        // Générez le PDF à partir du HTML
        $pdfContent = $dompdfService->generatePdf($invoiceContent);

        // Convertir le contenu du PDF en une chaîne Base64
        $pdfBase64 = base64_encode($pdfContent);

        // Envoyer le mail de confirmation
        $mail->send(
            'contact@gite-rain-du-pair.fr',
            $reservation->getEmail(), 
            '[GITE RAIN DU PAIR] Confimation de réservation',
            'confirm_reservation',
            [
                'reservation' => $reservation,
                'pdfBase64' => $pdfBase64, 
                'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') 
                . '/public/assets/img/logo-gite-rain-du-pair.png'),
            ],
        );

        // Envoyer un e-mail à l'administrateur
        $mail->sendAdminNotification(
            'contact@gite-rain-du-pair.fr',
            'admin@giteraindupair.com',
            'Nouvelle réservation confirmée',
            'admin_confirm_reservation',
            [
                'reservation' => $reservation,
            ],
        );

        // Envoi du sms de confirmation
        // $message ="Votre réservation pour le Gîte du Rain du Pair a été confirmé ! Retrouvez toutes vos informations de séjour sur votre compte. Merci pour votre confiance.";
        // $phone = $reservation->getPhone();

        // $sms->sendSms($phone, $message);
       
        $this->addFlash('success', 'La réservation a été confirmée avec succès.');

        return $this->redirectToRoute('admin_dashboard');
    }


    /**
    * Fonction pour refuser une réservation
    */
    #[Route('private-zone-224/dashboard/reservation/{id}/admin_refuse', name: 'admin_refuse_reservation')]
    public function refuseReservation(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ReservationRepository $reservationRepository,
        SendEmailService $mail,
        RefundService $refundService
    ): Response {

        // Récupérer la réservation
        $reservation = $reservationRepository->find($id);

        // Vérifier si la réservation est déjà refusée
        $isConfirm = $reservation->getIsConfirm();
        if (isset($isConfirm['status']) && $isConfirm['status'] === 'refusée') {
            $this->addFlash('warning', 'La réservation est déjà refusée.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Traitement du formulaire de refus
        if ($request->isMethod('POST')) {
            $reason = $request->request->get('reason', '');

            // Récupérer les informations de paiement
            $paymentMethod = $reservation->getPaymentMethod();
            $paymentIntentId = $reservation->getStripePaymentId();
            $refundAmount = $reservation->getTotalPrice(); // Remboursement intégral en cas de refus

            try {
                if ($paymentMethod === 'stripe') {
                    $refundService->processStripeRefund($paymentIntentId, $refundAmount);
                } else {
                    throw new \Exception('Méthode de paiement inconnue ou non prise en charge pour le remboursement.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du traitement du remboursement : ' . $e->getMessage());
                return $this->redirectToRoute('admin_dashboard');
            }

            // Mettre à jour le statut de la réservation
            $reservation->setIsConfirm(['status' => 'refusée']);
            $em->persist($reservation);
            $em->flush();

            // Envoyer un e-mail de notification
            $mail->send(
                'contact@gite-rain-du-pair.fr',
                $reservation->getEmail(),
                '[GITE RAIN DU PAIR] Réservation refusée',
                'reject_reservation',
                [
                    'reservation' => $reservation,
                    'reason' => $reason, // Inclure la raison dans le mail
                    'logo' => $this->imageToBase64($this->getParameter('kernel.project_dir') 
                    . '/public/assets/img/logo-gite-rain-du-pair.png'),
                ]
            );

            $this->addFlash('success', 'La réservation a été refusée et le remboursement est en cours.');
            return $this->redirectToRoute('admin_dashboard');
        }
    }


    /**
    * Fonction pour ajouter un avis
    */
    #[Route('private-zone-224/profil/{id}/review/{slug}', name: 'app_write_review')]
    public function writeReview(User $user, UserRepository $userRepository, ReservationRepository $reservationRepository, Request $request, int $id, string $slug, SendEmailService $mail, EntityManagerInterface $em): Response
    {
        $userSession = $this->getUser();
        $user = $userRepository->findOneBy(['id' => $id]);

        if($userSession == $user) {
            $review = new Review();

            $form = $this->createForm(ReviewType::class, $review);
            $form->handleRequest($request);

            // On modifie l'id de l'utilisateur
            $review->setUser($user);

            // On récupère la réservation concernée
            $reservation = $reservationRepository->findOneBy(['slug' => $slug]);
            $review->setReservation($reservation);

            if ($form->isSubmitted() && $form->isValid()) {
                $review = $form->getData();
                $em->persist($review);
                $em->flush();
                $this->addFlash('success', "Avis ajouté avec succès. Merci d\'avoir partagé votre expérience avec nous.");

                // Envoyer la notification à l'administrateur
                $mail->sendAdminNotification(
                    'contact@gite-rain-du-pair.fr',
                    'admin@giteraindupair.com',
                    'Nouvel avis à confirmer',
                    'admin_review',
                    [
                        'review' => $review,
                    ]
                );
                $userId = $user->getId();
                return $this->redirectToRoute('app_profil', ['id' => $userId]); 
            }
            return $this->render('security/profil_review.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        
        if($userSession != $user) {
            $this->addFlash('error', 'Accès refusé');
            return $this->redirectToRoute('app_home');
        }
    }

    /**
    * Fonction pour afficher la page des avis à valider
    */
    #[Route('private-zone-224/dashboard/review', name: 'admin_review')]
    public function showReview(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findBy(['is_verified' =>0], ['creation_date' => 'DESC']);

        return $this->render('admin/review.html.twig', [
            'reviews' => $reviews
        ]);
    }


    /**
    * Fonction pour valider un avis et lui répondre
    */
    #[Route('private-zone-224/dashboard/review/{id}', name: 'admin_review_verify')]
    public function verifiReview(ReviewRepository $reviewRepository, Request $request, int $id, EntityManagerInterface $em): Response
    {

        $review = $reviewRepository->find($id);

        // Mettre à jour le champ is_verified à true
        $review->setIsVerified(1);

        // Récupération de la réponse de l'admin depuis le formulaire
        $response = $request->request->get('response');
        $review->setResponse($response);
        $em->flush();

        $this->addFlash('success', 'Avis validé avec succès.');

        return $this->redirectToRoute('admin_menu');
    }


    /**
    * Fonction pour supprimer un avis
    */
    #[Route('private-zone-224/dashboard/review/{id}/delete', name: 'delete_review')]
    public function deleteReview(Review $review, EntityManagerInterface $em): Response
    {
          $em->remove($review);
          $em->flush();
  
          $this->addFlash('success', "Avis supprimé avec succès !");
  
          return $this->redirectToRoute('admin_menu');
    }


    /**
    * Fonction pour afficher la page du calendrier
    */
    #[Route('private-zone-224/dashboard/calendar', name: 'admin_calendar')]
    public function showCalendar(ReservationRepository $reservationRepository,
     Request $request, PeriodRepository $periodRepository, GiteRepository $giteRepository, EntityManagerInterface $em): Response
    {
         // Affichage des dates déjà réservées
         $reservations = $reservationRepository->findReservationsWithStatuses(['confirmée', 'en attente']);
         $datas = [];
         foreach($reservations as $reservation) {
             $datas[] = [
                 'id' => $reservation->getId(),
                 'start' => $reservation->getArrivalDate()->format('Y-m-d'),
                 'end' => $reservation->getDepartureDate()->format('Y-m-d'),
                 'title' => $reservation->getFirstName() . ' ' . $reservation->getLastName(), 
                 'color' => '#a9b4a4', 
                 'rendering' => 'background',
                 'type' => 'reservation',
                 'url' => $this->generateUrl('admin_reservation', ['id' => $reservation->getId()]) // Génère un lien
             ];
         }

        // Affichage des périodes de supplément
        $periods = $periodRepository->findBy([],['start_date' => 'ASC']);
        // Filtrer uniquement les périodes à venir
        $today = new \DateTime();
        $upcomingPeriods = array_filter($periods, function ($period) use ($today) {
            return $period->getEndDate() >= $today; 
        });
        // Ajouter les périodes de supplément
        foreach ($periods as $period) {
            $datas[] = [
                'id' => 'period-' . $period->getId(),
                'start' => $period->getStartDate()->format('Y-m-d'),
                'end' => $period->getEndDate()->format('Y-m-d'),
                'title' => 'Supplément ' . $period->getSupplement() . ' €',
                'color' => '#85634d', 
                'type' => 'period', 
            ];
        }


        // Ajout d'une période
        $newPeriod = new Period();
        $gite = $giteRepository->find(1);
        $newPeriod->setGite($gite);

        $form = $this->createForm(PeriodType::class, $newPeriod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPeriod = $form->getData(); 
            $startDate = $newPeriod->getStartDate();
            $endDate = $newPeriod->getEndDate();

            // Vérification des dates en BDD
            $overlappingPeriods = $periodRepository->findOverlappingPeriods($startDate, $endDate, $newPeriod->getId());

            if (!empty($overlappingPeriods)) {
                $this->addFlash('error', 'Les dates de début et de fin chevauchent une période existante.');
            } else {
            $em->persist($newPeriod);
            $em->flush();

            $this->addFlash('success', 'La période a été ajoutée avec succès.');
            return $this->redirectToRoute('admin_calendar');
        }
    }
        return $this->render('admin/calendar.html.twig', [
            'reservedDates' => json_encode($datas),
            'form' => $form,
            'periods' => $upcomingPeriods
        ]);
    }


    /**
    * Fonction pour afficher la page des statistiques
    */
    #[Route('private-zone-224/dashboard/statistics', name: 'admin_statistics')]
    public function showStatistics (ReviewRepository $reviewRepository, ReservationRepository $reservationRepository): Response
    {
        // Calculs des avis et moyenne
        $reviews = $reviewRepository->findBy(['is_verified' => 1], []);
        $averageRating = $reviewRepository->averageRating();

        // Recherche des réservations confimée
        $allReservations = $reservationRepository->findReservationsWithStatuses(['confirmée']);
        // Recherche des réservations annulée
        $allReservationsCancel = $reservationRepository->findReservationsWithStatuses(['annulée']);

       // Inclus 2025 et 2024
        $currentYear = (int)date('Y');
        $years = [$currentYear - 1, $currentYear];
    
        // Tableau avec toutes les données par mois
        $monthlyData = [];
        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                // Récupérer les réservations du mois
                $reservations = $reservationRepository->findReservationsByMonth($month, $year);

                if (!empty($reservations)) {
                    // Calcul du nombre total de nuits / mois
                    $daysInMonth = (int)(new \DateTime("$currentYear-$month-01"))->format('t'); 
                    // Génère les noms des mois
                    $monthsLabels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                    // Calcul des nuits réservées
                    $totalNightsReserved = 0;
                    foreach ($reservations as $reservation) {
                        $arrivalDate = $reservation->getArrivalDate();
                        $departureDate = $reservation->getDepartureDate();

                        // Ajuster les dates pour le mois en cours
                        $startOfMonth = new \DateTime("$year-$month-01");
                        $endOfMonth = (clone $startOfMonth)->modify('last day of this month');

                        // Limiter l'arrivée et le départ au mois en cours
                        if ($arrivalDate < $startOfMonth) {
                            $arrivalDate = $startOfMonth;
                        }
                        if ($departureDate > $endOfMonth) {
                            $departureDate = $endOfMonth;
                        }

                        // Calculer les nuits réservées pour ce mois
                        $nightsInMonth = $departureDate->diff($arrivalDate)->days + 1; // Ajout d'un 1 pour compter la dernière nuitée
                        $totalNightsReserved += $nightsInMonth;
                    }

                    $monthlyData[] = [
                        'month' => $monthsLabels[$month - 1],
                        'year' => $year,
                        'totalNightsReserved' => $totalNightsReserved,
                        'daysInMonth' => $daysInMonth,
                        'occupancyRate' => round(($totalNightsReserved / $daysInMonth) * 100, 2),
                    ];
                }
            }
        }
        return $this->render('admin/statistics.html.twig', [
            'reviews' => $reviews,
            'allReservations' => $allReservations,
            'allReservationsCancel' => $allReservationsCancel,
            'averageRating' => $averageRating,
            'monthlyData' => $monthlyData,
        ]);
    }


    /**
    * Fonction pour afficher la page des extras
    */
    #[Route('private-zone-224/dashboard/extra', name: 'admin_extra')]
    public function showExtra(ExtraRepository $extraRepository, EntityManagerInterface $em, Request $request, ReservationExtraRepository $reservationExtraRepository): Response
    {
        $extras = $extraRepository->findAll();
        $extra = new Extra();

        // Afficher les réservations avec extras
        $reservationsWithExtras = $reservationExtraRepository->findBy([], ['date' => 'ASC']);


        // Crée un formulaire pour Extra
        $form = $this->createForm(ExtraType::class, $extra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($extra);
            $em->flush();

            $this->addFlash('success', 'L\'extra a été créé avec succès.');

            return $this->redirectToRoute('admin_extra'); 
        }

        return $this->render('admin/extra.html.twig', [
            'extras' => $extras,
            'form' => $form->createView(),
            'reservationsWithExtras' => $reservationsWithExtras

        ]);
    }
}
