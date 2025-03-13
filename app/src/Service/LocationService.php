<?php

namespace App\Service;

use Symfony\Component\Intl\Countries;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocationService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Récupère les données de localisation depuis l'API OpenStreetMap 
     */
    public function getLocationData(string $postalCode, string $country): array
    {
        // Vérifier que les paramètres sont bien fournis
        if (!$postalCode || !$country) {
            return ['error' => 'Code postal et pays requis'];
        }

        // Vérifier que le code postal contient uniquement des chiffres (si le pays != Royaume Unis ou Canada)
        if (!in_array($country, ['Canada', 'Royaume-Uni']) && !ctype_digit($postalCode)) {
            // return new JsonResponse(['error' => 'Code postal invalide : doit contenir uniquement des chiffres'], 400);
            return ['error' => 'Code postal invalide : doit contenir uniquement des chiffres'];
        }

        // Vérifier les formats spécifiques pour le Royaume-Uni et le Canada
        if ($country === 'Royaume-Uni' && !preg_match('/^[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}$/i', $postalCode)) {
            // return new JsonResponse(['error' => 'Code postal invalide pour le Royaume-Uni'], 400);
            return ['error' => 'Code postal invalide pour le Royaume-Uni'];
        }

        if ($country === 'Canada' && !preg_match('/^[A-Z]\d[A-Z] \d[A-Z]\d$/i', $postalCode)) {
            // return new JsonResponse(['error' => 'Code postal invalide pour le Canada'], 400);
            return ['error' => 'Code postal invalide pour le Canada'];
        }

        // Formatage du pays pour l’URL
         // 🔥 Vérifier si le pays est bien reconnu par Symfony Intl
    $countryNames = array_flip(Countries::getNames()); // Inverser clés/valeurs pour retrouver le code ISO

    if (isset($countryNames[$country])) {
        $countryCode = $countryNames[$country]; // Ex: "France" -> "FR"
    } else {
        $countryCode = $country; // Si le pays n'est pas trouvé, garder la valeur d'origine
    }

        $geoNamesUsername = "mylenadlc";
        // URL de l'API 
        // $apiUrl = "https://nominatim.openstreetmap.org/search?q=$postalCode+$countryFormatted&format=json&addressdetails=1";
        // $apiUrl = "https://nominatim.openstreetmap.org/search?postalcode=$postalCode&country=$countryFormatted&format=json&addressdetails=1";
        $apiUrl = "http://api.geonames.org/postalCodeSearchJSON?postalcode=$postalCode&country=$countryCode&maxRows=10&username=$geoNamesUsername";

     
        // Envoi de la requête avec HttpClientInterface
        $response = $this->httpClient->request('GET', $apiUrl, [
            'headers' => [
                'User-Agent' => 'MonApplication/1.0 (contact@example.com)'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de l\'appel à Nominatim'];
        }

        // Convertir la réponse JSON en tableau PHP
        $data = $response->toArray();

        if (empty($data)) {
            return ['error' => 'Aucune ville trouvée pour ce code postal'];
        }

        $cities = [];
        
        foreach ($data['postalCodes'] as $entry) {
            if (isset($entry['placeName'])) {
                $cities[] = $entry['placeName'];
            }
        }
    
        return [
            'cities' => array_unique($cities),
            'country' => $countryCode
        ];
    }
}
