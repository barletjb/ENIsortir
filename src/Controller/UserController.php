<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/confirmed/{email}', name: '_confirmed')]
public function confirmation(string $email,EntityManagerInterface $em,Request $request,UserPasswordHasherInterface $passwordHasher,SluggerInterface $slugger): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $firstPassword = $user->getPassword();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $formUser = $this->createForm(UserType::class,$user);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {

            $photoFile = $formUser->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }

                $user->setPhoto($newFilename);
            }



            $user->setProfileCompleted(true);
            $password = $user->getPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_login');
        };

        return $this->render('user/confirmation.html.twig', [
            'form_User' => $formUser,
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

