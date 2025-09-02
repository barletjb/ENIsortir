<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin',name:'admin')]
final class VilleController extends AbstractController
{
    #[Route('/ville', name: 'app_ville')]
    public function index(): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    #[Route('/villes_list', name: '_villes_list')]
    public function list(VilleRepository $villeRepo, Request $request): Response
    {
        $search = $request->query->get('search');

        $queryBuilder = $villeRepo->createQueryBuilder('v');

        if ($search) {
            $queryBuilder->andWhere('v.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $villes = $queryBuilder->getQuery()->getResult();

        return $this->render('admin/villes.html.twig', [
            'villes' => $villes,
            'title' => 'Liste des villes',
            'add_route' => 'admin_villes_add',
            'search' => $search,
        ]);
    }

    #[Route('/villes/add', name: '_villes_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();
            return $this->redirectToRoute('admin_villes_list');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une ville',
        ]);
    }

    #[Route('/villes/{id}/edit', name: '_villes_edit')]
    public function edit(Ville $ville, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_villes_list');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier la ville',
        ]);
    }

    #[Route('/villes/{id}/delete', name: '_villes_delete')]
    public function delete(Ville $ville, EntityManagerInterface $em): Response
    {

        if(count($ville->getLieux())>0){
            $this->addFlash('danger', 'impossible de supprimer cette ville car elle est actuellement utilisée');
            return $this->redirectToRoute('admin_villes_list');
        }

        $em->remove($ville);
        $em->flush();
        return $this->redirectToRoute('admin_villes_list');
    }


}
