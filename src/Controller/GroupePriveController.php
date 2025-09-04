<?php

namespace App\Controller;

use App\Entity\GroupePrive;
use App\Entity\User;
use App\Form\GroupePriveType;
use App\Repository\GroupePriveRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/groupe-prive', name: 'groupe_prive')]
final class GroupePriveController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: '')]
    public function index(Request $request, GroupePriveRepository $groupePriveRepository): Response
    {
        $user = $this->getUser();
        $groupePrives = $groupePriveRepository->findByChef($user);

        return $this->render('groupe_prive/index.html.twig', [
            'user' => $user,
            'groupePrives' => $groupePrives,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/edit', name: '_nouveau', methods: ['GET', 'POST'])]
    public function nouveauGroupePrive(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $groupe = new GroupePrive();
        $groupe->setChefGroupe($user);
        $form = $this->createForm(GroupePriveType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($groupe);
            $em->flush();

            $this->addFlash('success', 'Groupe privé créé avec succès.');

            return $this->redirectToRoute('groupe_prive');
        }

        return $this->render('groupe_prive/edit.html.twig', [
            'groupeprive_form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/update/{id}', name: '_gerer', methods: ['GET', 'POST'])]
    public function gererGroupePrive(GroupePrive $groupePrive, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(GroupePriveType::class, $groupePrive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            $this->addFlash('success', 'Groupe modifié avec succès.');

            return $this->redirectToRoute('groupe_prive');

        }


        return $this->render('groupe_prive/manage_members.html.twig', [
            'groupePrive' => $groupePrive,
            'groupeprive_form' => $form->createView(),
        ]);
    }

}
