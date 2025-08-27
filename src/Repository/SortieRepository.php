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

    public function findByFiltres(array $criterias, $userId)
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

        if (!empty($criterias['organisateur']) && $criterias['organisateur'] === true) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $userId);
        }

        if (!empty($criterias['participant']) && $criterias['participant'] === true) {
            $qb->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $userId);
        }

        if (!empty($criteres['nonParticipant']) && $criteres['nonParticipant'] === true) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $userId);
        }

        if (!empty($criteres['sortiesPassees']) && $criteres['sortiesPassees'] === true) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }

}
