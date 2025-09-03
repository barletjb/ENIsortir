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

final class GroupePriveController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/groupe-prive', name: 'groupe_prive')]
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
    #[Route('/groupe-prive/edit', name: 'groupe_prive_nouveau', methods: ['GET', 'POST'])]
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
    #[Route('/groupe-prive/update/{id}', name: 'groupe_prive_gerer', methods: ['GET', 'POST'])]
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
//
//    #[IsGranted('ROLE_USER')]
//    #[Route('/groupe-prive/{groupeId}/manage-members', name: 'groupe_prive_manage_members', methods: ['GET', 'POST'])]
//    public function manageMembers(int $groupeId, Request $request, EntityManagerInterface $em, GroupePriveRepository $gr, UserRepository $ur): Response {
//        $groupe = $gr->find($groupeId);
//
//        if (!$groupe) {
//            throw $this->createNotFoundException('Groupe non trouvé.');
//        }
//
//        if ($groupe->getChefGroupe() !== $this->getUser()) {
//            throw $this->createAccessDeniedException('Accès refusé.');
//        }
//
//        $membresGroupes = $groupe->getUser()->toArray();
//        $membresGroupes[] = $groupe->getChefGroupe();
//
//        $usersNonMembres = $ur->createQueryBuilder('u')
//            ->where('u NOT IN (:membres)')
//            ->setParameter('membres', $membresGroupes)
//            ->getQuery()
//            ->getResult();
//
//
//
//        return $this->render('groupe_prive/manage_members.html.twig', [
//            'groupePrive' => $groupe,
//            'usersNonMembres' => $usersNonMembres,
//        ]);
//    }
//
//    #[IsGranted('ROLE_USER')]
//    #[Route('/groupe-prive/{groupeId}/add-user/{userId}', name: 'groupe_prive_add_user', methods: ['POST'])]
//    public function addUserToGroup(int $groupeId, int $userId, EntityManagerInterface $em): Response
//    {
//        $groupe = $em->getRepository(GroupePrive::class)->find($groupeId);
//        $user = $em->getRepository(User::class)->find($userId);
//
//        if (!$groupe || !$user) {
//            throw $this->createNotFoundException('Groupe ou utilisateur introuvable.');
//        }
//
//        if ($groupe->getUser()->contains($user)) {
//            $this->addFlash('warning', 'Utilisateur déjà membre.');
//            return $this->redirectToRoute('groupe_prive_manage_members', ['groupeId' => $groupeId]);
//        }
//
//        $groupe->addUser($user);
//        $em->flush();
//
//        $this->addFlash('success', 'Utilisateur ajouté au groupe.');
//
//        return $this->redirectToRoute('groupe_prive_manage_members', ['groupeId' => $groupeId]);
//    }
//
//    #[IsGranted('ROLE_USER')]
//    #[Route('/groupe-prive/{groupeId}/remove-user/{userId}', name: 'groupe_prive_remove_user', methods: ['POST'])]
//    public function removeUserFromGroup(int $groupeId, int $userId, EntityManagerInterface $em): Response
//    {
//        $groupe = $em->getRepository(GroupePrive::class)->find($groupeId);
//        $user = $em->getRepository(User::class)->find($userId);
//
//        if (!$groupe || !$user) {
//            throw $this->createNotFoundException('Groupe ou utilisateur non trouvé.');
//        }
//
//        if (!$groupe->getUser()->contains($user)) {
//            $this->addFlash('warning', 'Cet utilisateur ne fait pas partie du groupe.');
//            return $this->redirectToRoute('groupe_prive_manage_members', ['groupeId' => $groupeId]);
//        }
//
//        $groupe->removeUser($user);
//        $em->flush();
//
//        $this->addFlash('success', 'Utilisateur retiré du groupe avec succès.');
//
//        return $this->redirectToRoute('groupe_prive_manage_members', ['groupeId' => $groupeId]);
//    }
//

}
