<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * List of all the messages received with the contact form
     * @Route("/admin/contact", name="messages_contact")
     * @param ContactRepository $repository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ContactRepository $repository)
    {

        $contacts = $repository->findAllLatestMessages();

        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
            'current_menu' => 'admin',
        ]);
    }
}
