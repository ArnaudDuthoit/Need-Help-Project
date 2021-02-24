<?php

namespace App\Controller;

use App\Entity\PostLike;
use App\Entity\Projet;
use App\Entity\ProjetSearch;
use App\Entity\Messages;
use App\Form\ProjetSearchType;
use App\Repository\PostLikeRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjetController extends AbstractController
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
     * Index page of all the projects (with research function)
     * @Route("user/projets", name="projet.index")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {

        $search = new ProjetSearch();

        $form = $this->createForm(ProjetSearchType::class, $search);
        $form->handleRequest($request);

        #find and paginate all the project with search criteria
        $projets = $paginator->paginate(
            $this->repository->findAllActive($search),
            $request->query->getInt('page', 1), #Start page
            6 #number of projects per page
        );

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
            'form' => $form->createView(),
            'current_menu' => 'search'
        ]);
    }

    /**
     * Page with all the details of the project
     * @Route("user/projets/{slug}-{id}", name="projet.show", requirements={"slug": "[a-z0-9\-]*"})
     * @param Projet $projet
     * @param string $slug
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function show(Projet $projet, string $slug, Request $request)

    {

        if ($projet->getSlug() !== $slug) {
            return $this->redirectToRoute('projet.show', [
                'id' => $projet->getId(),
                'slug' => $projet->getSlug()
            ], 301);
        }

       $user =  $projet->getUser();

        #Form for sending a private message to the user
        $message = new Messages();
        # Add form fields
        $form = $this->createFormBuilder($message)
            ->add('content', TextareaType::class, array('label' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Entrez votre message')))
            ->add('Save', SubmitType::class, array('label' => 'Envoyer', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-top:15px')))
            ->getForm();
        # Handle form response
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form['content']->getData();
            # set form data
            $message->setContent($content);
            $message->setCreatedAt(new \DateTime());
            $message->setFromId($this->getUser());
            $message->setToId($user);
            //$contact->setMessage($message);
            # finally add data in database
            $sn = $this->getDoctrine()->getManager();
            $sn->persist($message);
            $sn->flush();

            $this->addFlash('success','Message privé bien envoyé !');
           return  $this->redirectToRoute('projet.show', [
                'id' => $projet->getId(),
                'slug' => $projet->getSlug()
            ]);

        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * For like or unlike a project
     * @Route("user/projets/{slug}-{id}/like", name="projet.like", requirements={"slug": "[a-z0-9\-]*"})
     * @param Projet $projet
     * @param EntityManagerInterface $manager
     * @param PostLikeRepository $likeRepository
     * @return Response
     */
    public function like(Projet $projet, EntityManagerInterface $manager, PostLikeRepository $likeRepository): Response

    {
        $user = $this->getUser(); #Get the logged in user

        if (!$user) return $this->json([ #if no user logged
            'code' => 403,
            'message' => 'Non autorisé'
        ], 403);

        #if the project already liked by the user => unlike the project
        if ($projet->isLikedByUser($user)) {
            $like = $likeRepository->findOneBy([
                'projet' => $projet,
                'user' => $user
            ]);
            $manager->remove($like);
            $manager->flush();

            return $this->json([
                'code' => 200,
                'message' => 'Like supprimé',
                'likes' => $likeRepository->count(['projet' => $projet])
            ], 200);
        }

        #if the user didn't like the project => add a like to this project
        $like = new PostLike();
        $like->setProjet($projet)
            ->setUser($user);

        $manager->persist($like);
        $manager->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Like ajouté',
            'likes' => $likeRepository->count(['projet' => $projet])
            ], 200);
    }
}
