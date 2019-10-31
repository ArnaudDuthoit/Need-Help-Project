<?php

namespace App\Controller;

use App\Entity\UserSearch;
use App\Form\UserInfosType;
use App\Form\UserResetPasswordType;
use App\Form\UserSearchType;
use App\Entity\Messages;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Projet;
use App\Form\ProjetType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;


class UserController extends AbstractController
{

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display all the projects for this user
     * @Route("/user/mesprojets", name="user.projet.index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function MyAccount()
    {
        #Get the current user logged in
        $user = $this->getUser();

        #Get all the projects published by the user
        $projets = $this->getDoctrine()->getRepository(Projet::class)->findAll();

        return $this->render('user/user.html.twig', [
            'user' => $user,
            compact('projets'),
            'current_menu' => 'settings'
        ]);
    }

    /**
     * Page for searching a user by is name
     * @Route("/user/list", name="user.index")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {

        $search = new UserSearch();
        $form = $this->createForm(UserSearchType::class, $search);
        $form->handleRequest($request);

        $user = $paginator->paginate(
            $this->repository->findAllUser($search), #find all the user with our search
            $request->query->getInt('page', 1), #starting page
            12 #users per page
        );

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'form' => $form->createView(),
            'users' => $user
        ]);
    }


    /**
     * Creating and publishing a new project
     * @Route("/user/new", name="user.projet.create")
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request, ObjectManager $manager)
    {

        $projet = new Projet();

        $form = $this->createForm(ProjetType::class, $projet);

        $form->handleRequest($request);

        if ($this->getUser()->getActive() == 1) { #if the user is active

            if ($form->isSubmitted() && $form->isValid()) {


                $user = $this->getUser();

                $projet->setUser($user);

                // if no title defined
                if($projet->getTitle() !== null)
                {
                    $manager->persist($projet);
                    $manager->flush();
                    $this->addFlash("success", " Projet publié avec succès");
                    return $this->redirectToRoute('user.projet.index');
                }
                else {
                    $this->addFlash("warning", "Veuillez entrer un titre pour votre projet ...");
                    return $this->render('user/new.html.twig', [
                        'current_menu' => 'new',
                        'projet' => $projet,
                        'form' => $form->createView()]);
                }
            }

            return $this->render('user/new.html.twig', [
                'current_menu' => 'new',
                'projet' => $projet,
                'form' => $form->createView()
            ]);
        }

        return $this->render('user/inactif.html.twig'); #if the user is not active
    }

    /**
     * The user editing his project page
     * @Route("/user/projet/{id}", name="user.projet.edit", methods="GET|POST")
     * @param Projet $projet
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Projet $projet, Request $request, ObjectManager $manager)
    {
        #We must check if the user CAN editing this project.Can editing ONLY his project
        if (!$this->isGranted('EDIT', $projet)) {
            return $this->redirectToRoute('user.projet.index');
        }

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", " Projet modifié avec succès");
            return $this->redirectToRoute('user.projet.index');
        }

        return $this->render('user/edit.html.twig', [
            'projet' => $projet,
            'form' => $form->createView()
        ]);
    }


    /**
     * Delete page of the selected project
     * @Route("user/projet/{id}", name="user.projet.delete" , methods="DELETE")
     * @param Projet $projet
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Projet $projet, Request $request, ObjectManager $manager)
    {

        #We must check if the user CAN deleting this project.Can deleting ONLY his project
        if (!$this->isGranted('DELETE', $projet)) {
            return $this->redirectToRoute('user.projet.index');
        }

        if ($this->isCsrfTokenValid('authenticate', $request->get('_token'))) { #check if the csrf token is valid

            $manager->remove($projet); // Remove the project
            $manager->flush();
            $this->addFlash("success", " Projet supprimé avec succès");

        }

        return $this->redirectToRoute('user.projet.index');

    }

    /**
     * User new password page
     * @Route("/user/new_pwd", name="user.new_pwd", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);

        #get data of the form
        $old_pwd = $form['old_password']->getData();
        $new_pwd = $form['new_password']->getData();

        $checkPass = $encoder->isPasswordValid($user, $old_pwd); #encode the old password enter by the user

        if ($form->isSubmitted() && $form->isValid()) {

            if ($checkPass === true) { #if old pass enter by user corresponding with his current password in the database
                $new_pwd_encode = $encoder->encodePassword($user, $new_pwd);
                $user->setPassword($new_pwd_encode); #set the new encode password in the database
                $this->addFlash("success", " Mot de Passe modifié avec succès");
            } else {
                $this->addFlash("error", " Ancien mot de passe incorrect ");
            }
            $manager->flush();
            return $this->redirectToRoute('user.new_pwd');
        }

        return $this->render('user/reset_pwd.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }


    /**
     * User editing his informations (username and mail adress)
     * @Route("/user/editprofile", name="user.editprofile", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editInfo(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserInfosType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash("success", " Informations modifiées avec succès");
            return $this->redirectToRoute('user.editprofile');
        }
        return $this->render('user/editprofile.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }


    /**
     * User delete his account page
     * @Route("/user/deleteprofile", name="user.deleteprofile", methods="GET|POST")
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserInfosType::class, $user);
        $form->handleRequest($request);

        #remove the user and new session
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->remove($user);
            $manager->flush();

            $session = $this->get('session');
            $session = new Session();
            $session->invalidate();
            return $this->redirectToRoute('home');
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }



    /**
     * Display all the informations of the user selected + form private messages
     * @Route("/user/{slug}-{id}", name="user.show", requirements={"slug": "[a-z0-9\-]*"})
     * @param User $user
     * @param string $slug
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function show(User $user, string $slug, Request $request)
    {

        if ($user->getSlug() !== $slug) {
            return $this->redirectToRoute('user.show', [
                'id' => $user->getId(),
                'slug' => $user->getSlug()
            ], 301);
        }


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
            # finally add data in database
            $sn = $this->getDoctrine()->getManager();
            $sn->persist($message);
            $sn->flush();

            $this->addFlash('success', 'Message privé bien envoyé !');
            return $this->redirectToRoute('user.show', [
                'id' => $user->getId(),
                'slug' => $user->getSlug()
            ]);

        }

        return $this->render('user/user.show.html.twig', [
            'controller_name' => 'ProjetController',
            'user' => $user,
            'form' => $form->createView(),
        ]);

    }

}
