<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $libelles = ['En création', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'];

        foreach ($libelles as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);


            switch ($libelle) {
                case 'Passée':
                    $this->addReference('etat_passée', $etat);
                    break;
                case 'Clôturée':
                    $this->addReference('etat_cloturée', $etat);
                    break;
                case 'Ouverte':
                    $this->addReference('etat_ouverte', $etat);
                    break;
            }
        }

        $manager->flush();
    }
}