<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sortie',name: 'sortie')]
final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }


    #[Route('/edit',name: '_edit')]
public function editSortie(Request $request, EntityManagerInterface $em) : Response
    {

        $sortie = new Sortie();
        $formSortie = $this->createForm(SortieType::class, $sortie);

        $formSortie->handleRequest($request);

        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuType::class, $lieu);

        $formLieu->handleRequest($request);


            if ($formSortie->isSubmitted() && $formSortie->isValid()) {
                $em->persist($sortie);
                $em->flush();

                $this->addFlash('success', 'Nouvelle sortie créée');
                return $this->redirectToRoute('sortie');
            }



            if ($formLieu->isSubmitted() && $formLieu->isValid()) {
                $em->persist($lieu);
                $em->flush();

                $this->addFlash('success', 'Nouveau lieu créé');
                return $this->redirectToRoute('sortie_edit');
            }




        return $this->render('sortie/edit.html.twig',[
            'lieu' => $sortie->getLieu(),
            'user' => $this->getUser(),
            'sortie_form' => $formSortie,
            'lieu_form' => $formLieu
        ]);

    }

}
