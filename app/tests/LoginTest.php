<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLoginIsSuccessfull(): void
    {
        $client = static::createClient();
    
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get('security.user_password_hasher');
    
        // Supprimer l'utilisateur s'il existe déjà
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'test@mail.fr']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
    
        // Créer un utilisateur test
        $user = new User();
        $user->setEmail('test@mail.fr');
        $user->setPassword($passwordHasher->hashPassword($user, 'secret'));
        $entityManager->persist($user);
        $entityManager->flush();
    
        $crawler = $client->request('GET', '/connexion');
    
        $form = $crawler->selectButton('Connexion')->form([
            'email' => 'test@mail.fr',
            'password' => 'secret',
        ]);
    
        $client->submit($form);
    
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
    
        // Debug si besoin
        // echo $client->getResponse()->getContent();

        $this->assertSelectorTextContains('h1', 'Évadez-vous en Alsace');
    }

    public function testIsLoginFailFailedWhenPasswordIsWrong(): void
    {

        $client = static::createClient();
    
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get('security.user_password_hasher');
    
        // Supprimer l'utilisateur s'il existe déjà
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'test@mail.fr']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
    
        // Créer un utilisateur test
        $user = new User();
        $user->setEmail('test@mail.fr');
        $user->setPassword($passwordHasher->hashPassword($user, 'secret'));
        $entityManager->persist($user);
        $entityManager->flush();
    
        $crawler = $client->request('GET', '/connexion');
    
        $form = $crawler->selectButton('Connexion')->form([
            'email' => 'test@mail.fr',
            'password' => 'secret_',
        ]);
    
        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();

        $this->assertRouteSame('app_login');

    }
    
}
