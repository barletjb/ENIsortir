<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user')]
final class UserController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/profil/{id}', name: '_profil')]
    public function afficherProfil(User $user): Response
    {
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/{id}/upload-photo', name: '_upload_photo')]
    public function uploadPhoto(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $file = $request->files->get('photo');
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('photo_directory'), $filename);
            $user->setPhoto($filename);
            $em->flush();

            $this->addFlash('success', 'Photo mise à jour.');
        }

        return $this->redirectToRoute('user_profil', ['id' => $user->getId()]);
    }



}
