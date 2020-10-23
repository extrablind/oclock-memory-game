<?php

namespace App\Repository;

use App\Entity\Memory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Memory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Memory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Memory[]    findAll()
 * @method Memory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Memory::class);
    }

    /**
     * @return Memory[] Returns an array of Memory objects
     */
    public function findHighScores($max = 3)
    {
        // Dql : doctrine query langage is abstracting db
        return $this->createQueryBuilder('m')
            // Big scores first
            ->orderBy('m.time', 'ASC')
            // Decide the nb of result to get
            ->setMaxResults($max)
            // Return query only to decide either get as array or pure object
            ->getQuery()
        ;
    }
}
