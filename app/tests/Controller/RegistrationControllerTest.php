<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testGetRequestToRegistrationPageReturnSuccessfulResponse(): void 
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');
    }

    public function testSpamBotsAreNotWelcome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm(
            "s'inscrire",
            [
                'registration_form[email]' => 'test@example.com',
                'registration_form[plainPassword][first]' => 'TestPassword123!',
                'registration_form[plainPassword][second]' => 'TestPassword123!',
                'registration_form[agreeTerms]' => true,
                'registration_form[numberPhone]' => 'boobies',
                'registration_form[numberFax]' => 'booooob',
            ]
        );

        // Vérifie la redirection vers /
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        // Vérifie qu'on n'est plus sur la page d'inscription
        
        $this->assertSelectorNotExists('form[name="registration_form"]'); 
    }
}