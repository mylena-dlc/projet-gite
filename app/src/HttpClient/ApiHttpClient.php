<?php

namespace App\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiHttpClient extends AbstractController
{

    private HttpClientInterface $httpClient;
    private string $apiUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = "https://actigo.vercel.app/"; 
    }

    public function getCategories()
    {
         try {
            $response = $this->httpClient->request('GET', "{$this->apiUrl}/api/category", [
                'verify_peer' => false
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}

