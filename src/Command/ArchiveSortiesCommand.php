<?php

namespace App\Command;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:archive-sorties')]
class ArchiveSortiesCommand extends Command
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oneMonthAgo = (new \DateTime())->modify('-1 month');
        $sorties = $this->sortieRepository->findOlderThan($oneMonthAgo);

        foreach ($sorties as $sortie) {
            $sortie->setArchived(true);
        }

        $this->em->flush();
        $output->writeln(count($sorties) . ' sorties archivées.');
        return Command::SUCCESS;
    }
}
