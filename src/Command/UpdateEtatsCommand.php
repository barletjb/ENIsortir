<?php

namespace App\Command;

use App\Repository\SortieRepository;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


# Commande manuelle => php bin/console app:update-etats


#[AsCommand(name: 'app:update-etats')]
class UpdateEtatsCommand extends Command
{
    public function __construct(private SortieRepository $sortieRepo,private EtatRepository $etatRepo,private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime('now',new \DateTimeZone('Europe/Paris'));

        $sorties = $this->sortieRepo->findBy(['archived' => false]);

        foreach ($sorties as $sortie) {
            $etat = $sortie->getEtat()->getLibelle();

            if ($etat === 'Annulée') {
                continue;
            }
            $dateCloture = $sortie->getDateLimiteInscription();
            $dateDebut = $sortie->getDateHeureDebut();
            $dateFin = (clone $dateDebut)->modify("+{$sortie->getDuree()} minutes");

            if ($now > $dateFin) {
                $sortie->setEtat($this->etatRepo->find(5)); // Passée
            } elseif ($now >= $dateDebut && $now <= $dateFin) {
                $sortie->setEtat($this->etatRepo->find(4)); // Activité en cours
            } elseif ($now > $dateCloture) {
                $sortie->setEtat($this->etatRepo->find(3)); // Clôturée
            }
        }
        $this->em->flush();
        $output->writeln('États mis à jour.');


        return Command::SUCCESS;
    }

}
