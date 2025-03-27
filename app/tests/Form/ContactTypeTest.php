<?php

namespace App\Tests\Form;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactTypeTest extends WebTestCase
{
    public function testIfSubmitContactFormIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Contactez-nous');

        // Récupérer le formulaire
        $submitButton = $crawler->selectButton('Envoyer');
        $form = $submitButton->form();

        $form["contact[email]"] = "test@mail.fr";
        $form["contact[subject]"] = "Titre du message";
        $form["contact[message]"] = "Bla Bla Bla";

        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier le statut HTTP
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Vérifier l'envoi du mail
        $this->assertEmailCount(1);

        $client->followRedirect();

        
        // Vérifier la présence du message de succès
        // $this->assertSelectorTextContains(
        //     '.alert.alert-success',
        //     'Votre message a été envoyé avec succès.'
        // );
        

        
    }
}
