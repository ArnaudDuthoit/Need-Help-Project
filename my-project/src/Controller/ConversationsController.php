<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Repository\MessagesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


class ConversationsController extends AbstractController
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
     * List of all the users (except the current user)
     * @Route("/user/conversations", name="conversations")
     * @param MessagesRepository $messagesRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(MessagesRepository $messagesRepository)
    {

        $users = $this->repository->findUsersWithoutMe($this->getUser()); #Get all users except me

        $unread = $messagesRepository->unreadCount($this->getUser()); #Get number of unreaded messages

        return $this->render('conversations/index.html.twig', [
            'users' => $users,
            'unread' => $unread,
            'current_menu' => 'conversations',
            'user'=>$this->getUser()

        ]);
    }


    /**
     * Display the conversation with the selected user + form for sending messages
     * @Route("/user/conversations/{id}", name="conversations.show")
     * @param User $user
     * @param Request $request
     * @param MessagesRepository $messagesRepository
     * @param PaginatorInterface $paginator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function show(User $user, Request $request, MessagesRepository $messagesRepository, PaginatorInterface $paginator)
    {

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

            return $this->redirectToRoute('conversations.show', array(
                'id' => $user->getId()));
        }

        $messages = $messagesRepository->getMessage($this->getUser(), $user); # Get messages between the 2 users

        $unread = $messagesRepository->unreadCount($this->getUser()); # Get the number of unreaded messages


        if (isset($unread)) {
            $messagesRepository->readAllFrom($this->getUser(), $user); # Put all the messages on "read_at"
            unset($unread[$user->getId()]);  # Set the number of unreaded messages to 0
        }

        $messages = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), #Starting page
            4 # Result per page
        );

        return $this->render('conversations/show.html.twig', [
            'current_menu' => 'conversations',
            'form' => $form->createView(),
            'user' => $user,
            'messages' => $messages,
            'unread' => $unread,
            'users' => $this->repository->findAll()]);
    }
}
