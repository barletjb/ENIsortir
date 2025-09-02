<?php

namespace App\Command;

/* Commande avec méthodologie CRON + Planificateur de tâche Windows */

//use App\Repository\SortieRepository;
//use Symfony\Component\Console\Attribute\AsCommand;
//use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Mailer\MailerInterface;
//use Symfony\Component\Mime\Email;
//use Twig\Environment;

//#[AsCommand(
//    name: 'app:rappel-mail',
//    description: 'Mail de rappel 48h avant la sortie',
//)]
//class RappelMailCommand extends Command
//{
//    public function __construct(private SortieRepository $sortieRepository, private MailerInterface $mailer, private Environment $twig)
//    {
//        parent::__construct();
//    }
//
//    protected function execute(InputInterface $input, OutputInterface $output): int
//    {
//        $debutJour = (new \DateTime('+2 days'))->setTime(0, 0);
//        $finJour  = (clone $debutJour)->modify('+1 day');
//
//
//        $sorties = $this->sortieRepository->findByDateBetween($debutJour, $finJour);
//
//        foreach ($sorties as $sortie) {
//            foreach ($sortie->getUsers() as $user) {
//                $email = (new Email())
//                    ->from('noreply@campus-eni.fr')
//                    ->to($user->getEmail())
//                    ->subject('Rappel : Votre sortie "' . $sortie->getNom() . '" est dans 2 jours !')
//                    ->html(
//                        $this->twig->render('emails/rappel.html.twig', [
//                            'user' => $user,
//                            'sortie' => $sortie
//                        ])
//                    );
//                $this->mailer->send($email);
//            }
//        }
//
//        $output->writeln('Rappels envoyés avec succès.');
//        return Command::SUCCESS;
//    }
//}


/* Commande avec méthodologie Messenger + Handler */
use App\Message\RappelMailMessage;
use App\Repository\SortieRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(name: 'app:rappel-mail')]
class RappelMailCommand extends Command
{
    private SortieRepository $sortieRepository;
    private MessageBusInterface $bus;

    private Environment $twig;

    private mailerInterface $mailer;

    public function __construct(SortieRepository $sortieRepository, MessageBusInterface $bus, MailerInterface $mailer, Environment $twig)
    {
        parent::__construct();
        $this->sortieRepository = $sortieRepository;
        $this->bus = $bus;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    protected function configure(): void
    {
        $this->setDescription('Envoi d\'un email de rappel d\'une sortie');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $debutJour = (new \DateTime('+2 days'))->setTime(0, 0);
        $finJour = (clone $debutJour)->modify('+1 day');

        $sorties = $this->sortieRepository->findByDateBetween($debutJour, $finJour);

        foreach ($sorties as $sortie) {
            $this->bus->dispatch(new RappelMailMessage($sortie->getId()));
            foreach ($sortie->getUsers() as $user) {
                $email = (new Email())
                    ->from('noreply@campus-eni.fr')
                    ->to($user->getEmail())
                    ->subject('Rappel : Votre sortie "' . $sortie->getNom() . '" est dans 2 jours !')
                    ->html(
                        $this->twig->render('emails/rappel.html.twig', [
                            'user' => $user,
                            'sortie' => $sortie
                        ])
                    );
                $this->mailer->send($email);
            }
        }

        $output->writeln('Rappels dispatchés avec succès.');
        return Command::SUCCESS;
    }
}

/* Commande pour tester les envois de mail à j-2 :

php bin/console app:rappel-mail

*/
