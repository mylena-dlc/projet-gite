<?php

namespace App\Controller;

use App\Service\LocationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LocationController extends AbstractController
{
    private LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }
    
    // #[Route('/api/get-location', name: 'get_location', methods: ['GET'])]
    // public function getLocation(Request $request): JsonResponse
    // {
    //     try {
    //         // Récupération du pays et du code postal renseignés
    //         $postalCode = $request->query->get('postalCode');
    //         $country = $request->query->get('country', 'France'); // Défaut à "France" si non défini

    //         // Utilisation du service pour récupérer les données
    //         $result = $this->locationService->getLocationData($postalCode, $country);

    //          // Vérifie si une erreur est retournée par le service
    //          if (isset($result['error'])) {
    //             return new JsonResponse($result, 400);
    //         }

    //         return new JsonResponse($result);

    //     } catch (\Exception $e) {
    //         return new JsonResponse([
    //             'error' => 'Erreur serveur : ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    #[Route('/api/get-cities', name: 'get_cities', methods: ['GET'])]
public function getCities(Request $request, LocationService $locationService): JsonResponse
{
    $postalCode = $request->query->get('postalCode');
    $country = $request->query->get('country');

    if (!$postalCode || !$country) {
        return new JsonResponse(['error' => 'Code postal et pays requis'], 400);
    }

    $result = $locationService->getLocationData($postalCode, $country);

    if (isset($result['error'])) {
        return new JsonResponse($result, 400);
    }

    return new JsonResponse(['cities' => $result['cities']]);
}

}
