<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Reservation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
    * Fonction pour afficher toutes les réservations avec les statuts "confirmée" et "en attente"
    */
    public function findReservationsWithStatuses(array $statuses)
    {
        return $this->createQueryBuilder('r')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') IN (:statuses)")
            ->setParameter('statuses', $statuses)
            ->orderBy('r.reservation_date', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
    * Fonction pour vérifier les chevauchements de dates de réservation en base de données
    */
    public function findOverlappingReservations(\DateTimeInterface $arrivalDate, 
    \DateTimeInterface $departureDate) 
    {
    // Initialise un objet QueryBuilder lié à l'entité des réservations
    $qb = $this->createQueryBuilder('r')
        // Vérifie si la date d'arrivée de la réservation en base 
        // est inférieure ou égale à la date de départ spécifiée
        ->where('r.departure_date >= :arrival_date')
        // Vérifie si la date de départ de la réservation en base 
        // est supérieure ou égale à la date d'arrivée spécifiée
        ->andWhere('r.arrival_date <= :departure_date')
        ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
        // Définit les valeurs des paramètres
        ->setParameter('arrival_date', $arrivalDate)
        ->setParameter('departure_date', $departureDate)
        ->setParameter('status', 'confirmée');


        // Exécute la requête et renvoie les résultats
        return $qb->getQuery()->getResult();
    }


    /**
    * Fonction pour afficher la réservation en cours 
    */
    public function findOngoingReservation()
    {
        $today = new \DateTime();
        $today->setTime(0, 0); // Remet l'heure à minuit pour éviter les erreurs d'heure

        return $this->createQueryBuilder('r')
            ->where('r.arrival_date <= :today')
            ->andWhere('r.departure_date >= :today')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->setParameter('today', $today)
            ->setParameter('status', 'confirmée')
            ->setMaxResults(1) // Limite à une seule réservation
            ->getQuery()
            ->getOneOrNullResult(); // Renvoie un seul résultat ou null
    }


    /**
    * Fonction pour afficher toutes les réservation passées
    */
    public function findPreviousReservations()
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('r')
            ->where('r.departure_date < :today')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->setParameter('today', $today)
            ->setParameter('status', 'confirmée')
            ->orderBy('r.departure_date', 'DESC')
            ->getQuery();

        return $qb->getResult();
    }


    /**
    * Fonction pour afficher toutes les réservation à venir
    */
    public function findUpcomingReservations()
    {
        $today = new \DateTime();
        $qb = $this->createQueryBuilder('r')
            ->where('r.departure_date > :today')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->andWhere('r.id NOT IN (
                SELECT res.id FROM App\Entity\Reservation res
                WHERE res.arrival_date <= :today 
                AND res.departure_date >= :today
            )') // Évite d'inclure une réservation en cours
            ->setParameter('today', $today)
            ->setParameter('status', 'confirmée')
            ->orderBy('r.departure_date', 'ASC')
            ->getQuery();

        return $qb->getResult();
    }


    /**
    * Fonction pour afficher toutes les réservations à confirmer par l'admin
    */
    public function findReservationsToConfirm()
    {
        return $this->createQueryBuilder('r')
        ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
        ->setParameter('status', 'en attente')
        ->orderBy('r.reservation_date', 'ASC')
        ->getQuery()
        ->getResult();
    }


    /**
    * Fonction pour afficher toutes les réservations refusées par l'admin
    */
    public function findReservationsRefused()
    {
        return $this->createQueryBuilder('r')
        ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
        ->setParameter('status', 'refusée')
        ->orderBy('r.reservation_date', 'ASC')
        ->getQuery()
        ->getResult();
    }

    /**
    * Fonction pour afficher toutes les réservations annulées
    */
    public function findReservationsCancel()
    {
        return $this->createQueryBuilder('r')
        ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
        ->setParameter('status', 'annulée')
        ->orderBy('r.reservation_date', 'ASC')
        ->getQuery()
        ->getResult();
    }

    /**
     * Récupère toutes les réservations d'un mois donné
     *
     * @param int $month Le mois à rechercher (1 = janvier, 12 = décembre)
     * @param int $year L'année à rechercher
     * @return array Retourne un tableau de réservations du mois donné
     */
    public function findReservationsByMonth(int $month, int $year): array
    {
        // Créer la date de début du mois donné
        $startDate = new \DateTime("$year-$month-01");
        // Créer la date de fin en utilisant 'last day of this month' pour récupérer le dernier jour du mois
        $endDate = (clone $startDate)->modify('last day of this month');

        return $this->createQueryBuilder('r')
            // Filtrer les réservations dont la date de départ est comprise entre le début et la fin du mois
            ->where('r.departure_date BETWEEN :start AND :end')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status") // Vérifie le statut "confirmée"
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('status', 'confirmée') 
            ->orderBy('r.departure_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    
    /**
    * Fonction pour trouver les demandes de réservations pour un utilisateur spécifique
    */
    public function findRequestForUser(User $user)
    {
 
        return $this->createQueryBuilder('r')
            ->Where("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->andWhere('r.user = :user') 
            ->setParameter('status', 'en attente')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult(); 
    }

    /**
    * Fonction pour trouver la réservation en cours pour un utilisateur spécifique
    */
    public function findOngoingReservationForUser(User $user)
    {
        $today = new \DateTime();
        $today->setTime(0, 0); // Remet l'heure à minuit pour éviter les erreurs d'heure

        return $this->createQueryBuilder('r')
            ->where('r.arrival_date <= :today')
            ->andWhere('r.departure_date >= :today')
            ->andWhere('r.user = :user') // Filtrer par utilisateur
            ->setParameter('today', $today)
            ->setParameter('user', $user)
            ->setMaxResults(1) // Limite à une seule réservation
            ->getQuery()
            ->getOneOrNullResult(); // Renvoie un seul résultat ou null
    }

    /**
    * Fonction pour trouver les réservations à venir pour un utilisateur spécifique
    */
    public function findUserUpcomingReservations(User $user)
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        $qb = $this->createQueryBuilder('r')
            ->where('r.arrival_date > :today')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->andWhere('r.user = :user')
            ->setParameter('today', $today)
            ->setParameter('status', 'confirmée')
            ->setParameter('user', $user)
            ->orderBy('r.arrival_date', 'ASC')
            ->getQuery();
           
            return $qb->getResult();

    }

    /**
    * Fonction pour trouver les réservations passées pour un utilisateur spécifique
    */
    public function findUserPreviousReservations(User $user)
    {
        $today = new \DateTime();
        $today->setTime(0, 0);

        $qb = $this->createQueryBuilder('r')
            ->where('r.departure_date < :today')
            ->andWhere('r.user = :user')
            ->andWhere("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->setParameter('today', $today)
            ->setParameter('user', $user)
            ->setParameter('status', 'confirmée')
            ->orderBy('r.departure_date', 'DESC')
            ->getQuery();
          
            return $qb->getResult();
    }


    /**
    * Fonction pour afficher les réservations refusée par l'admin pour un utilisateur
    */
    public function findReservationRefusedForUser(User $user)
    {
 
        return $this->createQueryBuilder('r')
            ->Where("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->andWhere('r.user = :user') 
            ->setParameter('status', 'refusée')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult(); 
    }


    /**
    * Fonction pour afficher les réservations annulées
    */
    public function findReservationCancelledForUser(User $user)
    {
 
        return $this->createQueryBuilder('r')
            ->Where("JSON_EXTRACT(r.is_confirm, '$.status') = :status")
            ->andWhere('r.user = :user') 
            ->setParameter('status', 'annulée')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult(); 
    }


    
}
