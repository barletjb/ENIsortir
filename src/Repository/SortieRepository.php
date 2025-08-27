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

    public function findByFiltres(array $criterias, $user)
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
            $qb->andWhere('s.date_heure_debut >= :dateDebut')
                ->setParameter('dateDebut', $criterias['dateDebut']);
        }

        if (!empty($criterias['dateFin'])) {
            $qb->andWhere('s.date_heure_debut <= :dateFin')
                ->setParameter('dateFin', $criterias['dateFin']);
        }

        if (!empty($criterias['organisateur']) && $criterias['organisateur'] === true) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if (!empty($criterias['participant']) && $criterias['participant'] === true) {
            $qb->join('s.participants', 'p')
                ->andWhere('p = :user')
                ->setParameter('user', $user);
        }

        if (!empty($criteres['nonParticipant']) && $criteres['nonParticipant'] === true) {
            $qb->leftJoin('s.participants', 'np')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->neq('np', ':userNonParticipant'),
                    $qb->expr()->isNull('np')
                ))
                ->setParameter('userNonParticipant', $user);
        }

        if (!empty($criteres['sortiesPassees']) && $criteres['sortiesPassees'] === true) {
                $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        } else {
                $qb->andWhere('s.dateHeureDebut >= :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }

}
