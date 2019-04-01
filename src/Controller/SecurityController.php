<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request)
    {
        if($request->isXmlHttpRequest()) {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            //dump($error->getMessageData());
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
            /*return $this->render('index.html.twig', [
                'last_username' => $lastUsername,
                'error'         => $error,
            ]);*/
        return new Response(
            json_encode([
                'last_username' => $lastUsername,
                'error'         => $error->getMessageData()[0],
                ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
        } else {
            return $this->render('index.html.twig');
            //return $this->redirect($this->generateUrl('your_route'));
        }
        
    }

    /**
     * @Route("/", name="index")
     */
    public function index(AuthenticationUtils $authenticationUtils)
    {
        if($request->isXmlHttpRequest()) {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            //dump($error->getMessageData());
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
            /*return $this->render('index.html.twig', [
                'last_username' => $lastUsername,
                'error'         => $error,
            ]);*/
        return new Response(
            json_encode([
                'last_username' => $lastUsername,
                'error'         => $error->getMessageData()[0],
                ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
        } else {
            return $this->render('index.html.twig');
            //return $this->redirect($this->generateUrl('your_route'));
        }
        /*return $this->render('index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);*/
    }

    /**
     * Route("/", name="app_login", methods={"GET", "POST"})
     */
    /*
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return new Response(json_encode([
            'error'         => $error,
        ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }
    */

    

    /**
     * @Route ("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('Will be intercepted before getting here');
    }
}
