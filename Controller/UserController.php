<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
                'IbtikarShareEconomyUMSBundle:User:login.html.twig', array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
                )
        );
    }

    /**
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     */
    public function verifyEmailAction(Request $request)
    {

    }

}
