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

        $titresSorties = [
            'Balade botanique au Parc de la Chézine',
            'Atelier cuisine végétarienne',
            'Soirée jeux de société à la médiathèque',
            'Randonnée urbaine à Saint-Herblain',
            'Initiation à la photographie de nuit',
            'Conférence sur la transition écologique',
            'Tournoi de pétanque interquartiers',
            'Projection-débat au Cinéma Le Lutétia',
            'Atelier tricot solidaire',
            'Yoga en plein air au Parc de la Bégraisière'
        ];

        $descriptions = [
            "Venez découvrir les plantes locales lors d'une balade guidée.",
            "Partagez vos astuces et recettes dans cet atelier participatif.",
            "Un moment convivial autour de jeux classiques et modernes.",
            "Explorez les rues de Saint-Herblain sous un nouvel angle.",
            "Une sortie idéale pour les amateurs de photo et de nature.",
            "Un événement pour échanger sur les enjeux climatiques actuels.",
            "Un tournoi amical pour tous les niveaux.",
            "Un débat citoyen suivi d’un apéro local.",
            "Rencontre intergénérationnelle autour du tricot.",
            "Détente et bien-être au cœur de la nature."


        ];

        for ($i = 0; $i < 15; $i++) {
            $sortie = new Sortie();

            $dateDebut = $faker->dateTimeBetween('+1 days', '+2 months');
            $dateLimite = (clone $dateDebut)->modify('-5 days');

            $index = $i % count($titresSorties);
            $sortie->setNom($titresSorties[$index]);
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDuree($faker->numberBetween(60, 240));
            $sortie->setDateLimiteInscription($dateLimite);
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($descriptions[$index]);
            $sortie->setOrganisateur($faker->randomElement($users));
            #$sortie->setLieu($faker->randomElement($lieux));
            $sortie->setSite($faker->randomElement($users)->getSite());

            $lieuxSaintHerblain = array_filter($lieux, fn($lieu) => str_contains($lieu->getNom(), 'Saint-Herblain'));
            $sortie->setLieu($faker->randomElement($lieuxSaintHerblain ?: $lieux));

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