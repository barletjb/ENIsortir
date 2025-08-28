<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Lieu;
use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = $manager->getRepository(User::class)->findAll();
        $lieux = $manager->getRepository(Lieu::class)->findAll();

        for ($i = 0; $i < 15; $i++) {
            $sortie = new Sortie();

            $dateDebut = $faker->dateTimeBetween('+1 days', '+2 months');
            $dateLimite = (clone $dateDebut)->modify('-5 days');

            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDuree($faker->numberBetween(60, 240));
            $sortie->setDateLimiteInscription($dateLimite);
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->paragraph());
            $sortie->setOrganisateur($faker->randomElement($users));
            $sortie->setLieu($faker->randomElement($lieux));
            $sortie->setSite($faker->randomElement($users)->getSite());

            $now = new \DateTime();
            if ($dateDebut < $now) {
                $sortie->setEtat($this->getReference('etat_passée', Etat::class));
            } elseif ($dateDebut < (clone $now)->modify('+7 days')) {
                $sortie->setEtat($this->getReference('etat_cloturée', Etat::class));
            } else {
                $sortie->setEtat($this->getReference('etat_ouverte', Etat::class));
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            UserFixtures::class,
            LieuFixtures::class,
        ];
    }
}