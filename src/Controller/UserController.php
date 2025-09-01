<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $formUser = $this->createForm(UserType::class,$user, [
            'validation_groups' => ['Default','enregistrement'],
            'password_required' => true,
        ]);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {
            $user->setProfileCompleted(true);

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


                $password = $formUser->get('password')->getData();
//
//            if ($passwordHasher->isPasswordValid($user, $password)) {
//                $this->addFlash('error', 'Vous devez changer votre mot de passe');
//            } else {
//                dd($password. " " . $user->getPassword());
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setProfileCompleted(true);
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success','Votre profil a bien été confirmé');
                return $this->redirectToRoute('app_login');



        };

        return $this->render('user/confirmation.html.twig', [
            'form_User' => $formUser,
            'user' => $user,
        ]);
    }

    #[Route('/update/{email}', name: '_update')]
    public function update(string $email,EntityManagerInterface $em,Request $request,UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $formUser = $this->createForm(UserType::class,$user,[
            'validation_groups' => ['Default'],
            'password_required' => false,
        ]);
        $formUser->handleRequest($request);

        if ($formUser->isSubmitted() && $formUser->isValid()) {

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
            }

            $password = $formUser->get('password')->getData();

            if($password !== null && $password !== ''){
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }


            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('sortie');
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

