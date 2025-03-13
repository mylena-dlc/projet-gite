<?php 

namespace App\EventListener;

use App\Entity\Reservation;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: 'prePersist')]
final class SlugListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /*
    * Fonction déclanché avant la persistance d'une entité pour générer un slug
    */
    public function prePersist(LifecycleEventArgs $args): void
    {
        // Récupération de l'entité concernée par l'événement
        $entity = $args->getObject();

        // Si l'entité est bien un objet Réservation, et s'il y a déjà un slug on ne le regénère pas
        if ($entity instanceof Reservation && !$entity->getSlug()) {
            // Génération du slug basé sur la référence, la ville et la date d'arrivée
            $slugBase = $entity->getReference()
                . '-' . strtolower($entity->getCity())
                . '-' . $entity->getArrivalDate()->format('Y-m-d');

            $slug = strtolower($this->slugger->slug($slugBase));

            // Vérifier si un slug identique existe déjà en base de données
            $entityManager = $args->getObjectManager();
            $repository = $entityManager->getRepository(Reservation::class);
            $existingSlug = $repository->findOneBy(['slug' => $slug]);

            if ($existingSlug) {
                $slug .= '-' . uniqid(); // Ajoute un identifiant unique en cas de doublon
            }

            $entity->setSlug($slug);
        }
    }
}