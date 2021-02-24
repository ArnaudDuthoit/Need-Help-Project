<?php

namespace App\Controller;

use App\Entity\Projet;

use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminProjetController extends AbstractController
{
    /**
     * @var ProjetRepository
     */
    private $repository;

    public function __construct(ProjetRepository $repository)
    {
        $this->repository = $repository;

    }


    /**
     * Admin Home Page
     * @Route("/admin", name="admin.projet.index")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {

        $projets = $paginator->paginate(

            $this->repository->findAll(),
            $request->query->getInt('page', 1), // Start Page
            6 // Projects per page
        );

        return $this->render('admin_projet/index.html.twig',[
                'projets' => $projets,
                'current_menu' => 'admin'
            ]
        );

    }

    /**
     * Admin Edit Page CRUD
     * @Route("/admin/projet/{id}", name="admin.projet.edit", methods="GET|POST")
     * @param Projet $projet
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Projet $projet, Request $request, EntityManagerInterface $manager)
    {

        $form = $this->createForm(ProjetType::class, $projet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", " Projet modifié avec succès");
            return $this->redirectToRoute('admin.projet.index');
        }

        return $this->render('admin_projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete selected project
     * @Route("admin/projet/{id}", name="admin.projet.delete" , methods="DELETE")
     * @param Projet $projet
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Projet $projet, Request $request, EntityManagerInterface $manager)
    {

        if ($this->isCsrfTokenValid('authenticate', $request->get('_token'))) { // check if csrf token is valid

            $manager->remove($projet); // Remove the project
            $manager->flush();
            $this->addFlash("success", " Projet supprimé avec succès");

        }

        return $this->redirectToRoute('admin.projet.index');

    }

}
