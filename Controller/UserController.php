<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type as formInputsTypes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

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
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $errorMessage = null;
        $successMessage = null;
        $formBuilder = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('loginCredentials', formInputsTypes\EmailType::class, array('attr' => array('autocomplete' => 'off'), 'constraints' => array(new Constraints\NotBlank(), new Constraints\Email())))
            ->add('save', formInputsTypes\SubmitType::class);
        $form = $formBuilder->getForm();
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $loginCredentials = $data['loginCredentials'];
                $message = $this->get('user_operations')->sendResetPasswordEmail($loginCredentials);
                if ($message === 'success') {
                    $successMessage = $message;
                } else {
                    $errorMessage = $message;
                }
            }
        }
        return $this->render('IbtikarShareEconomyUMSBundle:User:forgotPassword.html.twig', array(
                'successMessage' => $successMessage,
                'errorMessage' => $errorMessage,
                'form' => $form->createView(),
        ));
    }

    /**
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     */
    public function verifyEmailAction(Request $request)
    {

    }

    /**
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     */
    public function resetPasswordAction(Request $request)
    {

    }

}
