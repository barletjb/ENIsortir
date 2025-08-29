<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    #[Route('/admin/create-user', name: 'admin_user_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setIsActive(false);
            $user->setIsActif(true);
            $user->setProfileCompleted(false);
            $user->setRoles(['ROLE_USER']);

            $temporaryPassword = bin2hex(random_bytes(4));

            $hashedPassword = $passwordHasher->hashPassword($user, $temporaryPassword);
            $user->setPassword($hashedPassword);



            $email = (new Email())
                ->from('admin@campus-eni.fr')
                ->to($user->getEmail())
                ->subject('Création de votre compte')
                ->html(
                    $this->renderView('emails/user_created.html.twig', [
                        'user' => $user,
                        'temporaryPassword' => $temporaryPassword,
                    ])
                );

            $mailer->send($email);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès. Un email d’activation a été envoyé.');

            return $this->redirectToRoute('sortie');
        }


        return $this->render('admin/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
