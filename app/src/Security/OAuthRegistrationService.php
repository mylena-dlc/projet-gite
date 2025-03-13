<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final readonly class OAuthRegistrationService
{

    private EntityManagerInterface $entityManager;

    // Injection de l'EntityManager dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param GoogleUser $resourceOwner
     */
    public function persist(ResourceOwnerInterface $resourceOwner, UserRepository $repository): User
    {
        $user = (new User())
            ->setEmail($resourceOwner->getEmail())
            ->setGoogleId($resourceOwner->getId());

        // $repository->add($user, true);

          // Utilisation de l'EntityManager pour persister
          $this->entityManager->persist($user); // Persister l'entité
        $this->entityManager->flush(); // Sauvegarder en base de données
        return $user;
    }
}