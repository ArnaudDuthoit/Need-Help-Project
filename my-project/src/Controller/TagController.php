<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/tag")
 */
class TagController extends AbstractController
{
    /**
     * @var TagRepository
     */
    private $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;

    }


    /**
     * Get , Display and Paginate all the tags
     * @Route("/", name="tag_index", methods={"GET"})
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $tags = $paginator->paginate(

            $this->repository->findAll(), #Get all the tags
            $request->query->getInt('page', 1), #Starting page
            7 #Number of tags per page
        );

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
            'current_menu' => 'admin',
        ]);
    }

    /**
     * Adding a new tag
     * @Route("/new", name="tag_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            #Persist and flush the new tag in the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
            'current_menu' => 'admin'
        ]);
    }


    /**
     * Editing a tag
     * @Route("/{id}/edit", name="tag_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tag_index', [
                'id' => $tag->getId(),
            ]);
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
            'current_menu' => 'admin'
        ]);
    }

    /**
     * Deleting a tag
     * @Route("/{id}", name="tag_delete", methods={"DELETE"})
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    public function delete(Request $request, Tag $tag): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) { #check if csrf token is valid
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}
