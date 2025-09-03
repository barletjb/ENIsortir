<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $villes = [
            ['nom' => 'Saint-Herblain', 'codePostal' => '44800', 'ref' => 'saint_herblain'],
            ['nom' => 'Nantes', 'codePostal' => '44000', 'ref' => 'nantes'],
            ['nom' => 'Rezé', 'codePostal' => '44400', 'ref' => 'reze'],
            ['nom' => 'Orvault', 'codePostal' => '44700', 'ref' => 'orvault'],
            ['nom' => 'Bouguenais', 'codePostal' => '44340', 'ref' => 'bouguenais'],
            ['nom' => 'Sautron', 'codePostal' => '44880', 'ref' => 'sautron'],
            ['nom' => 'Indre', 'codePostal' => '44610', 'ref' => 'indre'],
            ['nom' => 'Clisson', 'codePostal' => '44190', 'ref' => 'clisson'],
            ['nom' => 'Haute-Goulaine', 'codePostal' => '44115', 'ref' => 'haute_goulaine'],
            ['nom' => 'Champtoceaux', 'codePostal' => '49270', 'ref' => 'champtoceaux'],
        ];

        foreach ($villes as $index => $data) {
            $ville = new Ville();
            $ville->setNom($data['nom']);
            $ville->setCodePostal($data['codePostal']);
            $manager->persist($ville);

            $this->addReference("ville_$index", $ville);
            $this->addReference($data['ref'], $ville);

        }

        $manager->flush();
    }
}