<?php

namespace App\Repository;

use App\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
    * Fonction pour afficher les tokens actifs
    */
        public function findActiveTokens(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.is_active = :active')
            ->andWhere('t.expiration_date >= :today')
            ->setParameter('active', true)
            ->setParameter('today', new \DateTime('now'))
            ->orderBy('t.expiration_date', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
    * Fonction pour afficher les tokens inactifs
    */
    public function findInactiveTokens(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.is_active = :active')
            ->andWhere('t.expiration_date >= :today')
            ->setParameter('active', false)
            ->setParameter('today', new \DateTime('now'))
            ->orderBy('t.expiration_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
    * Fonction pour afficher les tokens expirés
    */
    public function findExpirateTokens(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.expiration_date < :today')
            ->setParameter('today', new \DateTime('now'))
            ->orderBy('t.expiration_date', 'DESC')
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return Token[] Returns an array of Token objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Token
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
