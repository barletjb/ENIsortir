<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\CsvImportType;
use App\Form\SiteType;
use App\Form\UserType;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $formCsv = $this->createForm(CsvImportType::class);
        $formCsv->handleRequest($request);

        if ($formCsv->isSubmitted() && $formCsv->isValid()) {
            $uploadedFile = $formCsv->get('csv')->getData();


            $csvPath = $uploadedFile->getRealPath();
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            foreach ($records as $record) {

                if (empty($record['email']) || !filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
                    $logger->error('email vide');
                    continue;
                }

                if (!preg_match('/^[a-zA-Z0-9._%+-]+@campus-eni\.fr$/', $record['email'])) {
                    $logger->error('email pattern');
                    continue;
                }

                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $record['email']]);
                if ($existingUser) {
                    $logger->error('existe déjà');
                    continue;
                }

                $site = null;
                if (!empty($record['site'])) {
                    $site = $em->getRepository(Site::class)->find($record['site']);
                    if (!$site) {
                        $logger->error('pas de site');
                        continue;
                    }
                }

                $user = new User();
                $user->setEmail($record['email']);
                $user->setNom($record['nom'] ?? '');
                $user->setPrenom($record['prenom'] ?? '');
                $user->setPseudo($record['pseudo'] ?? $record['email']);
                $user->setTelephone($record['telephone'] ?? null);
                $user->setSite($site);

                $password = $record['password'] ?? 'Password123!';
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);

                $logger->error('user créé'.$record['email']);

                $em->persist($user);

            }
            $em->flush();
            $this->addFlash('success','Import terminé');
            return $this->redirectToRoute('admin_users_list');
        }
            return $this->render('admin/import_csv.html.twig', [
                'formCsv' => $formCsv,
            ]);


    }


    #[Route('/users_list', name: '_users_list')]
    public function usersList(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher) : Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $users = $em->getRepository(User::class)->findAll();
    return $this->render('admin/users_list.html.twig', [
        'users' => $users,
    ]);
}



#[Route('/user/{id}/delete', name: '_user_delete')]
public function userDelete(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $user = $user->getId();

    if ($user){
        $userDeleted = $em->getRepository(User::class)->findOneBy(['id' => $user]);
        $em->remove($userDeleted);
        $em->flush();
        $this->addFlash('success','Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users_list');
    }

    return $this->render('admin/user_delete.html.twig', []);


}

#[Route('/sites_list', name: '_sites_list')]
public function list(SiteRepository $siteRepo, VilleRepository $villeRepo, Request $request): Response
{
    $villeId = $request->query->get('ville');
    $villes = $villeRepo->findAll();

    $sites = $villeId
        ? $siteRepo->findBy(['ville' => $villeId])
        : $siteRepo->findAll();

    return $this->render('admin/sites.html.twig', [
        'sites' => $sites,
        'villes' => $villes,
        'selectedVille' => $villeId,
    ]);
}
    #[Route('/sites/add', name: '_sites_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($site);
            $em->flush();
            return $this->redirectToRoute('admin_sites_list');

        }

        return $this->render('sites/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un site',
        ]);
    }

    #[Route('/sites/{id}/edit', name: '_sites_edit')]
    public function edit(Site $site, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_sites_list');
        }

        return $this->render('sites/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le site',
        ]);
    }

    #[Route('/sites/{id}/delete', name: '_sites_delete')]
    public function delete(Site $site, EntityManagerInterface $em): Response
    {
        $em->remove($site);
        $em->flush();
        return $this->redirectToRoute('admin_sites_list');
    }




}



