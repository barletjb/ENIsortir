<?php

namespace App\Repository;

use App\Entity\GroupePrive;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupePrive>
 */
class GroupePriveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupePrive::class);
    }

    public function findByChef(User $chef): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.chefGroupe = :chef')
            ->setParameter('chef', $chef)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
