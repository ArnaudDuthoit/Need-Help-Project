<?php

namespace App\Controller;

use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Utils\Token;
use Swift_SmtpTransport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;



class SecurityController extends AbstractController
{

    /**
     * Registration page
     * @Route("/inscription",name="security_registration")
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registration(Request $request,ObjectManager $manager, UserPasswordEncoderInterface $encoder){
        $user = new User();
        $form= $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);

            #Generate a token for the activation account and put it in the base
            $tokenGenerator = new Token();
            $token = $tokenGenerator->generateToken();
            $user->setRegistrationToken($token);

            $user->setActive(0); #set the new user to inactive

            $user->setRole('ROLE_USER'); #Set a default role for all the new users
            $manager->persist($user);
            $manager->flush();

            #Send a email with the registration token
            $mail = $user->getEmail();
            $token = $user->getRegistrationToken();

            $MAILER_USERNAME = $_ENV['MAILER_USERNAME'];
            $MAILER_PASSWORD = $_ENV['MAILER_PASSWORD'];

            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, "ssl"))
                ->setUsername($MAILER_USERNAME)
                ->setPassword($MAILER_PASSWORD);

            $mailer = new \Swift_Mailer($transport);

            #Generate an URL with the registration token in it
            $url = $this->generateUrl('register', array('RegisterToken' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message ('L équipe NeedHelp'))
                ->setSubject('Confirmation de votre inscription')
                ->setFrom('NeedHelpProjet@gmail.com')
                ->setTo($mail)
                ->setBody(
                    $this->renderView(
                        'security/mail_confirm.html.twig',
                        [
                            'url' => $url
                        ]),
                    'text/html'
                );

            $mailer->send($message);
            $this->addFlash("success", " Inscription réussie ! Vous pouvez désormais vous connecter.");
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig',[
            'form' => $form->createView()
        ]);
    }
    /**
     * Get the Registration Token for account activation
     * @Route("/register", name="register", methods = {"GET"})
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, ObjectManager $manager)
    {
        // $_GET parameters
        $token = $request->get('RegisterToken'); #Get the token in the url

        $user = $this->getDoctrine()->getRepository(User::class);

        #Find a user in the database with the token that we get
        $res = $user->findOneBy(['RegistrationToken' => $token]);

        if ($res) { #if we find a user with this token -> activate this account

            $res->setActive(1);
            echo $res->getActive();
            $manager->flush();
            $this->addFlash("success", "Validation terminée ! 
            Vous pouvez désormais vous connecter et publier vos projets.");
            return $this->redirectToRoute('home');

        } else  #if we didn't find a user (wrong token)

        {
            return $this->render('security/login.html.twig');

        }
    }


    /**
     * Page for password forgotten by the user
     * @Route("/forgottenPassword", name="forgotten_password")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function forgottenPassword(Request $request)
    {

        if ($request->isMethod('POST')) {  #Check if the adresse mail is in the database

            $email = $request->request->get('email');

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user === null) { #if not in the database
                $this->addFlash('danger', 'Adresse mail inconnue');
                return $this->redirectToRoute('forgotten_password');
            }

            #Generate a new token that it will be our reset token password
            $tokenGenerator = new Token();
            $token = $tokenGenerator->generateToken();

            try { #Send the reset password token in the database for the user
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('home');
            }
            #config swiftmailer

            $MAILER_USERNAME = $_ENV['MAILER_USERNAME'];
            $MAILER_PASSWORD = $_ENV['MAILER_PASSWORD'];

            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465, "ssl"))
                ->setUsername($MAILER_USERNAME)
                ->setPassword($MAILER_PASSWORD);

            #Generate an URL with the reset password token in it
            $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $mailer = new \Swift_Mailer($transport);

            #Sending a mail with the url
            $message = (new \Swift_Message ('L équipe NeedHelp'))
                ->setSubject('Reset Mot de passe')
                ->setFrom('NeedHelpProjet@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'security/mail_reset.html.twig',
                        [
                            'url' => $url
                        ]),
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('success', 'Mail envoyé ! Veuillez consulter vos mails.');
            return $this->render('security/forgotten_password.html.twig');
        }

        return $this->render('security/forgotten_password.html.twig');
    }

    /**
     * Get the reset password token after the user clicked on the url in the mail
     * @Route("/reset_password/{token}", name="reset_password")
     * @param Request $request
     * @param string $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isMethod('POST')) { #Try to find a user with this Reset password Token in the base
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

            if ($user === null) { #If no user with it => wrong token in the url
                $this->addFlash('danger', 'Mauvais lien de réinitialisation de mot de passe.Veuillez consulter vos mails.');
                return $this->redirectToRoute('reset_password',
                    ['token' => 'error']);
            }
            #if we find a user with this reset password token
            $user->setResetToken(null); #Remove this token of the database

            #Send the new encrypted password to the database
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();

            $this->addFlash('notice', 'Mot de passe mis à jour avec succès ! Vous pouvez vous connecter avec votre nouveau mot de passe.');
            return $this->redirectToRoute('home');
        } else {
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }


    /**
     * @Route("/login",name="security_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authenticationUtils){
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security\login.html.twig',[
            'last_username' => $lastUsername,
            'error'=>$error
        ]);
    }


    /**
     * @Route("/logout",name="security_logout")
     */
    public function logout(){}

}
