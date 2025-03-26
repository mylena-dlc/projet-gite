<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageContentAndReservationLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Vérifie que la page d'accueil renvoie un code 200
        $this->assertResponseIsSuccessful();

        // Vérifie qu'un lien "Réserver un séjour" existe
        $this->assertSelectorExists('a:contains("Réserver un séjour")');

        // Vérifie le titre
        $this->assertSelectorTextContains('h1', 'Évadez-vous en Alsace dans un gîte de charme avec bain nordique, idéal pour 4 personnes');

        // Vérifie que le bouton pointe vers /recherche-reservation
        $link = $crawler->selectLink('Réserver un séjour')->link();
        $this->assertStringContainsString('/recherche-reservation', $link->getUri());

        // Clique sur le lien et vérifie que la page cible est bien chargée
        $crawler = $client->click($link);
        $this->assertResponseIsSuccessful();

        // Vérifie que la page cible contient un formulaire 
        $this->assertSelectorExists('form'); 
    }
}
