<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $noms = ["Nantes", "Rennes", "Quimper", "Niort"];

        foreach ($noms as $index => $nom) {
            $site = new Site();
            $site->setNom($nom);

            $manager->persist($site);
            $this->addReference("site_$index", $site);
        }

        $manager->flush();
    }
}