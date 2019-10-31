<?php

namespace App\Controller;


use App\Entity\User;
use App\Repository\ProjetRepository;
use App\Utils\Token;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Contact;
use Swift_SmtpTransport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class HomeController extends AbstractController
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
     * Home Page
     * @Route("/", name="home")
     * @param ProjetRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ProjetRepository $repository)
    {
        $projetliked = $repository->findMostLiked(); #Get most liked projects

        $last = $repository->findLatest(); #Get the lastest projects published

        return $this->render('home/home.html.twig', [
            'projetliked' => $projetliked,
            'last' => $last,
        ]);
    }

    /**
     * Ranking complete of all the most liked projects
     * @Route("/ranking", name="ranking")
     * @param ProjetRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ranking(ProjetRepository $repository)
    {

        $projets = $repository->topLiked(); #Get all the project likes order by DESC

        return $this->render('home/ranking.html.twig', [
            'controller_name' => 'HomeController',
            'projetliked' => $projets
        ]);
    }

    /**
     * List of all the lastest projects published
     * @Route("/lastest", name="lastest")
     * @param ProjetRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lastest(ProjetRepository $repository)
    {

        $lastest = $repository->findAllLatest(); #Get all the project order by last posted

        return $this->render('home/lastest.html.twig', [
            'lastest' => $lastest
        ]);
    }


    /**
     * Contact Form Page
     * @Route("/contact", name="contact")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request) {
        $contact = new Contact;
        # Add form fields
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label' => 'Nom', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('email', TextType::class, array('label' => 'Email', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('subject', TextType::class, array('label' => 'Objet', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('message', TextareaType::class, array('label' => 'Message', 'attr' => array('class' => 'form-control')))
            ->add('Save', SubmitType::class, array('label' => 'Envoyer', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-top:15px')))
            ->getForm();
        # Handle form response
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { #Get Data for all the inputs form
            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $subject = $form['subject']->getData();
            $message = $form['message']->getData();

            # set form data
            $contact->setName($name);
            $contact->setEmail($email);
            $contact->setSubject($subject);
            $contact->setMessage($message);
            $contact->setCreatedAt(new \DateTime());
            # finally add data in database
            $sn = $this->getDoctrine()->getManager();
            $sn->persist($contact);
            $sn->flush();

            $MAILER_USERNAME = $_ENV['MAILER_USERNAME'];
            $MAILER_PASSWORD = $_ENV['MAILER_PASSWORD'];


            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, "ssl")) #Config SwiftMailer
                ->setUsername($MAILER_USERNAME)
                ->setPassword($MAILER_PASSWORD);

            $mailer = new \Swift_Mailer($transport);

            $message = (new \Swift_Message ('L équipe NeedHelp'))  #Config of the email
                ->setSubject($subject)
                ->setFrom('NeedHelpProjet@gmail.com')
                ->setTo($email)
                ->setBody($this->renderView('home/sendemail.html.twig'), 'text/html');
            $mailer->send($message);

            return $this->render('home/contact_finish.html.twig', [

            ]);

        }

        return $this->render('home/form.html.twig', [
            'current_menu' => 'contact',
            'form' => $form->createView()]);

    }


    /**
     * General Data Protection Regulation page
     * @Route("/rgpd", name="RGPD")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function RGPD() {
        return $this->render('home/RGPD.html.twig');
    }

    /**
     * The terms and conditions page
     * @Route ("/mentions", name="mentions")
     */
    public function mentions(){
        return $this->render('home/mentions.html.twig');
    }

    /**
     * Q&A page
     * @Route ("/faq", name="faq")
     */
    public function faq(){
        return $this->render('home/faq.html.twig');
    }

}
