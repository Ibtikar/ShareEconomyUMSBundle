<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type as formInputsTypes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

class UserController extends Controller
{

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return Response
     */
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
     * @param Request $request
     * @return Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $translator = $this->get('translator');
        $formBuilder = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('email', formInputsTypes\EmailType::class, array('attr' => array('autocomplete' => 'off'), 'constraints' => array(new Constraints\NotBlank(), new Constraints\Email())))
            ->add('Retrieve your password', formInputsTypes\SubmitType::class);
        $form = $formBuilder->getForm();
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $email = $data['email'];
                $message = $this->get('user_operations')->sendResetPasswordEmail($email);
                if ($message === 'success') {
                    $this->addFlash('success', $translator->trans('A message have been sent to your email with a link to change your password page'));
                } else {
                    $this->addFlash('error', $message);
                }
            }
        }
        return $this->render('IbtikarShareEconomyDashboardDesignBundle:Layout:not_loggedin_form.html.twig', array(
                'form' => $form->createView(),
                'title' => $translator->trans('Forgot your password'),
        ));
    }

    /**
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     */
    public function verifyEmailAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->findOneBy(['email' => $request->get('email'), 'emailVerificationToken' => $request->get('token')]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $now = new \DateTime();
        $translator = $this->get('translator');

        if ($user->getEmailVerificationTokenExpiryTime() < $now) {
            $this->addFlash('error', $translator->trans('The link expired please request a new one.'));
        } else {
            $this->get('user_operations')->verifyUserEmail($user);
            $this->addFlash('success', $translator->trans('Email verified successfully.'));
        }

        return $this->render('IbtikarShareEconomyDashboardDesignBundle:Layout:not_loggedin.html.twig');
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @param string $email
     * @param string $token
     * @return Response
     */
    public function resetPasswordAction(Request $request, $email, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->findOneBy(array('email' => $email, 'changePasswordToken' => $token));
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $currentTime = new \DateTime();
        $translator = $this->get('translator');
        if ($user->getChangePasswordTokenExpiryTime() < $currentTime) {
            $this->addFlash('error', $translator->trans('The change password link expired please request a new one.'));
            return $this->render('IbtikarShareEconomyDashboardDesignBundle:Layout:not_loggedin.html.twig');
        }
        if (!$user->getEmailVerified()) {
            $this->get('user_operations')->verifyUserEmail($user);
        }
        $formBuilder = $this->createFormBuilder($user, array(
                'validation_groups' => 'resetPassword',
            ))
            ->setMethod('POST')
            ->add('userPassword', formInputsTypes\RepeatedType::class, array(
                'type' => formInputsTypes\PasswordType::class,
                'required' => true,
                'first_options' => array('label' => 'Password', 'attr' => array('autocomplete' => 'off')),
                'second_options' => array('label' => 'Repeat Password', 'attr' => array('autocomplete' => 'off')),
            ))
            ->add('Reset your password', formInputsTypes\SubmitType::class);
        $form = $formBuilder->getForm();
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setValidPassword();
                $user->setChangePasswordToken(null);
                $user->setForgetPasswordRequests(0);
                $user->setChangePasswordTokenExpiryTime(null);
                $em->flush($user);
                $this->addFlash('success', $translator->trans('Password changed sucessfully.'));
                return $this->render('IbtikarShareEconomyDashboardDesignBundle:Layout:not_loggedin.html.twig');
            }
        }
        return $this->render('IbtikarShareEconomyDashboardDesignBundle:Layout:not_loggedin_form.html.twig', array(
                'form' => $form->createView(),
                'title' => $translator->trans('Reset your password'),
        ));
    }
}
