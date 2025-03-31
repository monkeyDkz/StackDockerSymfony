<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute(in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'back_default_index' : 'app_default_home');
        // }

               // get the login error if there is one
               $error = $authenticationUtils->getLastAuthenticationError();

               // last username entered by the user
               $lastUsername = $authenticationUtils->getLastUsername();
       
               return $this->render('security/login.html.twig', [
                   'last_username' => $lastUsername,
                   'error' => $error,
               ]);
    }

    #[Route(path: '/login/{provider}', name: 'connect_oauth')]
    public function loginOauth(ClientRegistry $clientRegistry, string $provider): Response
    {
        $client = $clientRegistry->getClient($provider);
        $scope = ["profile", "email"];

        return $client->redirect($scope, []);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}