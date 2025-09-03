<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $ville = [
            'saint_herblain',
            'nantes',
            'reze',
            'orvault',
            'bouguenais',
            'sautron',
            'indre',
        ];

        $villes = [];
        foreach ($ville as $ref) {
            $villes[] = $this->getReference($ref, Ville::class);
        }


        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude(47.2, 47.3));
            $lieu->setLongitude($faker->longitude(-1.7, -1.6));
            $lieu->setVille($faker->randomElement($villes));

            $manager->persist($lieu);

            $this->addReference("lieu_$i", $lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}