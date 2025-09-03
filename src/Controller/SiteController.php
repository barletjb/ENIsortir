<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin',name:'admin')]
final class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_site')]
    public function index(): Response
    {
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }

    #[Route('/sites_list', name: '_sites_list')]
    public function list(SiteRepository $siteRepo, VilleRepository $villeRepo, Request $request): Response
    {
        $villeId = $request->query->get('ville');
        $search = $request->query->get('search');

        $villes = $villeRepo->findAll();
        $queryBuilder = $siteRepo->createQueryBuilder('s');

        if ($villeId) {
            $queryBuilder->andWhere('s.ville = :ville')
                ->setParameter('ville', $villeId);
        }

        if ($search) {
            $queryBuilder->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $sites = $queryBuilder->getQuery()->getResult();
        return $this->render('admin/sites.html.twig', [
            'sites' => $sites,
            'villes' => $villes,
            'title' => 'Liste des sites',
            'add_route' => 'admin_sites_add',
            'selectedVille' => $villeId,
            'search' => $search,
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

        return $this->render('admin/edit.html.twig', [
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

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le site',
        ]);
    }

    #[Route('/sites/{id}/delete', name: '_sites_delete')]
    public function delete(Site $site, EntityManagerInterface $em): Response
    {
        $siteParticipant = $em->getRepository(User::class)->findBy(['site' => $site->getId()]);
        $siteSortie = $em->getRepository(Sortie::class)->findBy(['site' => $site->getId()]);

        if (!$siteParticipant and !$siteSortie) {
            $em->remove($site);
            $em->flush();
        } else {
            $this->addFlash('danger','Vous ne pouvez pas supprimer ce site car il est déjà utilisé');
        }



        return $this->redirectToRoute('admin_sites_list');
    }




}
