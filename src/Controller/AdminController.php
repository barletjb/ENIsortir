<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/create-user', name: 'admin_user_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setConfirmationToken(uniqid('token_', true));

            $user->setIsActive(false);
            $user->setProfileCompleted(false);

            $user->setRoles(['ROLE_USER']);

            $user->setPassword('');

            $em->persist($user);
            $em->flush();

            // ENVOYER MAIL CONFIRMATION

            $this->addFlash('success', 'Utilisateur créé avec succès. Un email d’activation a été envoyé.');

            return $this->redirectToRoute('sortie');
        }

        return $this->render('admin/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
