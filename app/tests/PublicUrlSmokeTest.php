<?php

namespace App\Tests;

use App\Tests\TestTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicUrlSmokeTest extends WebTestCase
{
    use TestTrait;

    private KernelBrowser $client;

    /**
    * Initialise le client HTTP avant chaque test
    */
    public function setUp(): void
    {
        $this->client = $this->createClientAndFollowRedirects();
    }


    /**
    * Test que toutes les pages publiques sont accessibles
    */
    public function testAllPagesAreLoadedSuccessfully(): void
    {
        $publicURIs = $this->getPublicURI();
        $failedURIs = [];
    
        echo "\n Routes publiques testées :\n";
    
        foreach ($publicURIs as $routeName => $uri) {
            echo "- $routeName ($uri)\n";
    
            $this->client->request('GET', $uri);
            $statusCode = $this->client->getResponse()->getStatusCode();
    
            if ($statusCode !== Response::HTTP_OK) {
                $failedURIs[] = [
                    'name' => $routeName,
                    'uri' => $uri,
                    'code' => $statusCode,
                ];
            }
        }
        if (!empty($failedURIs)) {
            echo "\n Routes publiques en erreur :\n";
            foreach ($failedURIs as $fail) {
                echo "- {$fail['name']} ({$fail['uri']}) → code {$fail['code']}\n";
            }
        }
        $this->assertEmpty($failedURIs, 'Certaines pages publiques ne sont pas accessibles.');
    }
    

    /**
    * Retourne les routes publiques
    */
    public function getPublicURI(): array 
    {
        $router = static::getContainer()->get('router');
        $routes = $router->getRouteCollection()->all();
    
        $publicURIs = [];
    
        foreach ($routes as $routeName => $route) {
            if ($route->getDefault('_public_access') === true) {
                $publicURIs[$routeName] = $route->getPath();
            }
        }
        
        return $publicURIs;
    }
}