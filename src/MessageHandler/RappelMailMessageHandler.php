<?php

namespace App\MessageHandler;

use App\Message\RappelMailMessage;
use App\Repository\SortieRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class RappelMailMessageHandler
{
    private SortieRepository $sortieRepository;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(SortieRepository $sortieRepository, MailerInterface $mailer, Environment $twig)
    {
        $this->sortieRepository = $sortieRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function __invoke(RappelMailMessage $message)
    {
        $sortie = $this->sortieRepository->find($message->getSortieId());
        if (!$sortie) {
            return;
        }

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
}