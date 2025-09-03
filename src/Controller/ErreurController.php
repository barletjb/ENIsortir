<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ErreurController extends AbstractController
{
    #[Route('/erreur', name: 'erreur')]
    public function index(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error.html.twig');
    }

    #[Route('/erreur/{code}', name: 'erreur_code')]
    public function testErreur(string $code): Response
    {
        $template = match ($code) {
            '404' => 'bundles/TwigBundle/Exception/error404.html.twig',
            '500' => 'bundles/TwigBundle/Exception/error500.html.twig',
            default => 'bundles/TwigBundle/Exception/error.html.twig',
        };

        return $this->render($template);
    }
}
