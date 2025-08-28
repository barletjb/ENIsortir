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
            ['nom' => 'Saint-Herblain', 'codePostal' => '44800'],
            ['nom' => 'Nantes', 'codePostal' => '44000'],
            ['nom' => 'Rezé', 'codePostal' => '44400'],
            ['nom' => 'Orvault', 'codePostal' => '44700'],
            ['nom' => 'Bouguenais', 'codePostal' => '44340'],
            ['nom' => 'Sautron', 'codePostal' => '44880'],
            ['nom' => 'Indre', 'codePostal' => '44610'],
        ];

        foreach ($villes as $index => $data) {
            $ville = new Ville();
            $ville->setNom($data['nom']);
            $ville->setCodePostal($data['codePostal']);
            $manager->persist($ville);

            // ✅ Ajout de la référence
            $this->addReference("ville_$index", $ville);
        }

        $manager->flush();
    }
}
