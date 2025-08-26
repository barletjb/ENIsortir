<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;




#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: '')]
    #[IsGranted('ROLE_USER')]

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










    #[Route('/edit',name: '_edit')]
public function editSortie(Request $request, EntityManagerInterface $em) : Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Nouvelle sortie créée');
            return $this->redirectToRoute('sortie');
        }

        return $this->render('sortie/edit.html.twig',[
            'sortie_form' => $form,]);

    }

}
