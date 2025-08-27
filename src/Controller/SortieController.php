<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: '')]
    #[IsGranted('ROLE_USER')]
    public function index(SortieRepository $sortieRepository): Response

    {
        $sorties = $sortieRepository->findAll();
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
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
        $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);

        # A modifier quand user sera opé
        $site = $em->getRepository(Site::class)->findOneBy(['id' => 1]);
        $orga = $em->getRepository(User::class)->findOneBy(['id' => 1]);

        $sortie = new Sortie();
        $sortie->setEtat($etat);
        # A modifier quand user sera opé
        $sortie->setSite($site);
        $sortie->setOrganisateur($orga);
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
//            return $this->redirectToRoute('sortie_edit');
        }

        return $this->render('sortie/edit.html.twig',[
            'lieu' => $sortie->getLieu(),
            'user' => $this->getUser(),
            'sortie_form' => $formSortie,
            'lieu_form' => $formLieu
        ]);
    }


    #[Route('/lieu/details/{id}', name: 'lieu_details', methods: ['GET'])]
    public function details(int $id, LieuRepository $lieuRepository): JsonResponse
    {
        $lieu = $lieuRepository->find($id);

        if (!$lieu) {
            return new JsonResponse(['error' => 'Lieu non trouvé'], 404);
        }

        return new JsonResponse([
            'ville' => $lieu->getVille()->getNom(),
            'rue' => $lieu->getRue(),
            'codePostal' => $lieu->getVille()->getCodePostal(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
        ]);
    }

    #[Route('/lieu/add', name: '_lieu_add', methods: ['POST'])]
    public function addLieu(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'errors' => (string) $form->getErrors(true, false)
        ], 400);
    }


}
