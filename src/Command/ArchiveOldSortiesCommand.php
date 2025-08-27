<?php

namespace App\Command;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ArchiveOldSorties',
    description: 'Archive toutes les sorties ayant eu lieu il y a plus de X jours',
)]
class ArchiveOldSortiesCommand extends Command
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EntityManagerInterface $em,
        private EtatRepository $etatRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                'Nombre de jours à partir duquel archiver',
                30
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = $input->getArgument('days');
        $dateLimite = new \DateTime("-$days days");

        $sorties = $this->sortieRepository->findSortiesTermineesAvant($dateLimite);

        foreach ($sorties as $sortie) {
            $this->em->remove($sortie);
        }

        $this->em->flush();


        return Command::SUCCESS;
    }
}

