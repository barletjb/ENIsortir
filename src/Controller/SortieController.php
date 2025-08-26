<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie')]
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_sortie_annuler')]
    public function annuler(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie introuvable.');
        }

        $participant = $this->getUser();

        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdmin()) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette sortie.');
            return $this->redirectToRoute('app_sortie');
        }

        if ($request->isMethod('POST')) {
            $motif = $request->get('motif');
            $sortie->setEtat('Annulé');
            $sortie->setInfosSortie(($sortie->getInfosSortie() ?? '') . " Annulée, motif: " . $motif);

            $em->flush();

            $this->addFlash('success', 'La sortie a été annulée.');
            return $this->redirectToRoute('app_sortie');
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie
        ]);
    }








}
