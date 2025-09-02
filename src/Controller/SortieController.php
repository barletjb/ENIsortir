<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\LieuType;
use App\Form\RechercheIndexType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(Request $request, SortieRepository $sortieRepository, MobileDetector $mobileDetector): Response
    {
        if($mobileDetector->isMobile()) {
            $this->addFlash('info', 'La création de sortie est non accessible sur smartphone');
        }

        $user = $this->getUser();
        $form = $this->createForm(RechercheIndexType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $criterias = $form->getData();
            //$userId = $this->getUser()->getId();
            $sorties = $sortieRepository->findByFiltre($criterias, $this->getUser());
        } else {

            $now = new \DateTime();
            $limitDate = (clone $now)->modify('-30 days');

            $sorties = $sortieRepository->createQueryBuilder('s')
                ->where('s.dateHeureDebut >= :limitDate')
                ->andWhere('s.archived = false')
                ->setParameter('limitDate', $limitDate)
                ->getQuery()
                ->getResult();
        }

        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'form_filtre' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/profil/{id}', name: '_user_profil')]
    public function afficherProfil(User $user): Response
    {
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/inscription', name: '_inscription', methods: ['POST'])]
    public function inscription(Sortie $sortie, EntityManagerInterface $em, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getUser();
        $nbUsers = count($sortie->getUsers());
        $date = new \DateTime();

        if($user->isActif()){
            if ($date <= $sortie->getDateLimiteInscription()) {
                if ($nbUsers < $sortie->getNbInscriptionsMax()) {
                    if (!$sortie->getUsers()->contains($user)) {
                        $sortie->addUser($user);

                        $email = (new Email())
                            ->from('admin@campus-eni.fr')
                            ->to($user->getEmail())
                            ->subject('Inscription à la sortie " '. $sortie->getNom() . " \" .")
                            ->html(
                                $this->renderView('emails/user_inscription.html.twig', [
                                    'user' => $user,
                                    'sortie'=> $sortie,
                                ])
                            );
                        $mailer->send($email);

                        $em->persist($sortie);
                        $em->flush();
                        $this->addFlash('success', 'Inscription réussie !');
                    } else {
                        $this->addFlash('info', 'Vous êtes déjà inscrit(e).');
                    }
                } else {
                    $this->addFlash('info', 'Le nombre maximal de personne est atteint. Une prochaine fois, peut-être...');
                }
            } else {
                $this->addFlash('info', 'La date de clôture des inscriptions est atteinte !');
            }
        } else {
            $this->addFlash('danger','En tant que membre banni vous ne pouvez pas vous inscrire à une sortie');
        }
        return $this->redirectToRoute('sortie');

    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/desistement', name: '_desistement', methods: ['POST'])]
    public function desistement(Sortie $sortie, EntityManagerInterface $em, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getUser();

        if ($sortie->getUsers()->contains($user)) {
            $email = (new Email())
                ->from('admin@campus-eni.fr')
                ->to($user->getEmail())
                ->subject('Désistement à la sortie " '. $sortie->getNom() . " \" .")
                ->html(
                    $this->renderView('emails/user_desistement.html.twig', [
                        'user' => $user,
                        'sortie'=> $sortie,
                    ])
                );
            $mailer->send($email);
            $sortie->removeUser($user);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Désistement réussi !');
        } else {
            $this->addFlash('info', 'Vous n\'êtes pas inscrit(e) à cette sortie.');
        }

        return $this->redirectToRoute('sortie');

    }


    #[IsGranted('ROLE_ORGA')]
    #[Route('/{id}/annulation', name: '_annulation', methods: ['POST'])]
    public function annulation(Sortie $sortie, Request $request, EntityManagerInterface $em, EtatRepository $etatRepository ): RedirectResponse {
        $user = $this->getUser();

        if (!$user || ($sortie->getOrganisateur() !== $user && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            $this->addFlash('error', 'Vous n\'avez pas le droit d\'annuler cette sortie.');
            return $this->redirectToRoute('sortie');
        }

        if ($sortie->getEtat()->getLibelle() === 'Activité en cours') {
            $this->addFlash('error', 'La sortie ne peut pas être annulée car elle est en cours.');
            return $this->redirectToRoute('sortie');
        }

        if ($sortie->getEtat()->getLibelle() === 'Annulée') {
            $this->addFlash('error', 'La sortie est déjà annulée.');
            return $this->redirectToRoute('sortie');
        }

        $raison = $request->request->get('raison_annulation');
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));
        $sortie->setRaisonAnnulation($raison);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Sortie annulée avec succès : ' . $raison);

        return $this->redirectToRoute('sortie');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/edit', name: '_edit')]
    public function editSortie(Request $request, EntityManagerInterface $em): Response
    {
        $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'En création']);

        $orga = $em->getRepository(User::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $site = $em->getRepository(Site::class)->findOneBy(['id' => $this->getUser()->getSite()->getId()]);

        $sortie = new Sortie();
        $sortie->setEtat($etat);

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


    #[IsGranted('ROLE_USER')]
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



    #[IsGranted('ROLE_USER')]
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

    #[IsGranted('ROLE_ORGA')]
    #[Route('/{id}/update', name: '_update')]
    public function updateSortie(Sortie $sortie, Request $request, EntityManagerInterface $em): Response
    {

        $orga = $em->getRepository(User::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $site = $em->getRepository(Site::class)->findOneBy(['id' => $this->getUser()->getSite()->getId()]);

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

            $this->addFlash('success', 'La modification de votre sortie a bien été effectuée');
            return $this->redirectToRoute('sortie');
        }

        if ($formLieu->isSubmitted() && $formLieu->isValid()) {
            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Nouveau lieu créé');
        }

        return $this->render('sortie/update.html.twig',[
            'lieu' => $sortie->getLieu(),
            'user' => $this->getUser(),
            'sortie_form' => $formSortie,
            'lieu_form' => $formLieu
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: '_detail')]
    public function detail(int $id, EntityManagerInterface $em): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if ($sortie->isArchived()) {
            throw $this->createNotFoundException('Cette sortie est archivée et n’est plus consultable.');
        }

        if (!$sortie) {

            $this->addFlash('error', 'La sortie demandée n\'existe pas.');

            return $this->redirectToRoute('sortie');
        }


        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[IsGranted('ROLE_ORGA')]
    #[Route('/{id}/publication', name: '_publication', methods: ['GET'])]
    public function publication(Sortie $sortie, EntityManagerInterface $em, EtatRepository $etatRepository ): RedirectResponse {
        $user = $this->getUser();

        if (!$user || ($sortie->getOrganisateur() !== $user)) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de publier cette sortie.');
            return $this->redirectToRoute('sortie');
        }

        if ($sortie->getEtat()->getLibelle() != 'En création') {
            $this->addFlash('info', 'La sortie ne peut pas être publier.');
            return $this->redirectToRoute('sortie');
        }

        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Sortie a été publié');

        return $this->redirectToRoute('sortie');
    }

}
