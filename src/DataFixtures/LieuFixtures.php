<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $villes = [];
        for ($i = 0; $i < 7; $i++) {
            $villes[] = $this->getReference("ville_$i");
        }


        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude(47.2, 47.3));
            $lieu->setLongitude($faker->longitude(-1.7, -1.6));
            $lieu->setVille($faker->randomElement($villes));

            $manager->persist($lieu);
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
