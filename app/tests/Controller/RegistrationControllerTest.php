<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    use TestTrait;
    
    public function testGetRequestToRegistrationPageReturnSuccessfulResponse(): void 
    {
        $this->clientGoesOnRegisterPage();

        $this->assertResponseIsSuccessFul();

        $this->assertSelectorTextContains('h1', 'Créer un compte');

    }

    public function testSpamBotsAreNotWelcome(): void
    {
        $client = $this->clientGoesOnRegisterPage();
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
        $this->assertResponseStatusCodeSame(403, 'Go away dirty bot !');
        $this->assertRouteSame('app_register');
    }

    private function clientGoesOnRegisterPage(): KernelBrowser
    {
        $client = $this->createClientAndFollowRedirects();
        
        $client->request('GET', '/register');

        return $client;
    }

}