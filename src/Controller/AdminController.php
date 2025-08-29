<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CsvImportType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin',name:'admin')]
final class AdminController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    #[Route('/create-user', name: '_user_create')]
    public function create(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface             $mailer
    ): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsActif(true);
            $user->setProfileCompleted(false);
            $user->setRoles(['ROLE_USER']);
            $user->setPhoto('default.jpg');

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


    #[Route('/import-users', name: '_import_users')]
    public function importUsers(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher,LoggerInterface $logger): Response
    {
        $requireHeaders = ['email', 'nom', 'prenom', 'pseudo', 'password'];

        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv_file')->getData();

            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                $header = fgetcsv($handle, 1000, ',');

                $requiredColumns = array_diff($requireHeaders, $header);
                if (count($requiredColumns) > 0) {
                    $this->addFlash('error', 'Il manque une/des colonnes : ' . implode(', ', $requiredColumns));
                    return $this->redirectToRoute('admin_import_users');
                }

                $lineNumber = 1;
                $errors = [];

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $lineNumber++;
                    $row = array_combine($header, $data);

                    try {
                        foreach ($requireHeaders as $column) {
                            if (empty($row[$column])) {
                                throw new \Exception("La colonne '$column' de la ligne $lineNumber est vide.");
                            }
                        }

                        $existingUser = $em->getRepository(User::class)->findUsers(
                            $row['email'],
                            $row['pseudo'],
                            $row['telephone'] ?? ''
                        );

                        if (!empty($existingUser)) {
                            throw new \Exception("L'email '{$row['email']}' et/ou le pseudo '{$row['pseudo']}' et/ou le téléphone '{$row['telephone']}' existe déjà.");
                        }

                        $user = new User();
                        $user->setEmail($row['email']);
                        $user->setNom($row['nom']);
                        $user->setPrenom($row['prenom']);
                        $user->setPseudo($row['pseudo'] ?? $row['email']);
                        $user->setTelephone($row['telephone'] ?? null);
                        $user->setProfileCompleted(false);
                        $user->setPhoto('uploads/photo/default.jpg');
                        $user->setRoles(['ROLE_USER']);
                        $user->setIsActif(true);

                        $password = $row['password'] ?? 'Password123!';
                        $hashedPassword = $passwordHasher->hashPassword($user, $password);
                        $user->setPassword($hashedPassword);

                        $em->persist($user);

                    } catch (\Exception $e) {
                        $errors[$lineNumber] = $e->getMessage();
                        $logger->error("Erreur import ligne {$lineNumber}: " . $e->getMessage());
                    }
                }

                fclose($handle);

                if (empty($errors)) {
                    $em->flush();
                    $this->addFlash('success', 'Importation terminée avec succès.');
                    return $this->redirectToRoute('sortie');
                } else {
                    foreach ($errors as $line => $message) {
                        $this->addFlash('error', "Ligne $line : $message");
                    }
                }

            } else {
                $this->addFlash('error', 'Erreur lors de l’ouverture du fichier CSV.');
            }
        }

        return $this->render('admin/import_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
