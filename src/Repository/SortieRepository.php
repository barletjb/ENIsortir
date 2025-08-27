<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }



    public function findByFiltre(array $criterias, $user): array
    {
        $qb = $this->createQueryBuilder('s');


        if (!empty($criterias['site'])) {
            $qb->andWhere('s.site = :site')
                ->setParameter('site', $criterias['site']);
        }

        if (!empty($criterias['rechercheNom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criterias['rechercheNom'] . '%');
        }


        if (!empty($criterias['dateDebut'])) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $criterias['dateDebut']);
        }

        if (!empty($criterias['dateFin'])) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $criterias['dateFin']);
        }


        if (!empty($criterias['organisateur'])) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if (!empty($criterias['participant'])) {
            $qb->andWhere(':user MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        if (!empty($criterias['nonParticipant'])) {
            $qb->andWhere(':user NOT MEMBER OF s.users')
                ->setParameter('user', $user);
        }

        if (!empty($criterias['sortiesPassees'])) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        } else {
            $qb->andWhere('s.dateHeureDebut >= :now')
                ->setParameter('now', new \DateTime());
        }


        $qb->orderBy('s.dateHeureDebut', 'ASC');
        return $qb->getQuery()->getResult();
    }


}
