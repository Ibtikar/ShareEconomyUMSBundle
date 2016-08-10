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
                    $this->addFlash('success', $message);
                } else {
                    $this->addFlash('error', $message);
                }
                return $this->render('IbtikarShareEconomyUMSBundle:User:message.html.twig', ['layout' => $this->getParameter('ibtikar_share_economy_ums.frontend_layout')]);
            }
        }
        return $this->render('IbtikarShareEconomyUMSBundle::form.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Forgot your password',
        ));
    }

    /**
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     */
    public function verifyEmailAction(Request $request)
    {
        $em   = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->findOneBy(['email' => $request->get('email'), 'emailVerificationToken' => $request->get('token')]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $now = new \DateTime();

        if ($user->getEmailVerificationTokenExpiryTime() < $now) {
            $this->addFlash('error', 'انتهت صلاحية استخدام هذا الرابط, من فضلك قم بطلب رابط تأكيد جديد.');
        } else {
            $this->get('user_operations')->verifyUserEmail($user);
            $this->addFlash('success', 'تم تفعيل البريد الإلكتروني بنجاح.');
        }

        $layout = $this->getParameter('ibtikar_share_economy_ums.frontend_layout');

        return $this->render('IbtikarShareEconomyUMSBundle:User:message.html.twig', ['layout' => $layout]);
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
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->findOneBy(array('email' => $email, 'changePasswordToken' => $token));
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $currentTime = new \DateTime();
        $translator = $this->get('translator');
        $layout = $this->getParameter('ibtikar_share_economy_ums.frontend_layout');
        if ($user->getChangePasswordTokenExpiryTime() < $currentTime) {
            $this->addFlash('error', $translator->trans('The change password link expired please request a new one.'));
            return $this->render('IbtikarShareEconomyUMSBundle:User:message.html.twig', ['layout' => $layout]);
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
            ->add('Change', formInputsTypes\SubmitType::class);
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
                return $this->render('IbtikarShareEconomyUMSBundle:User:message.html.twig', ['layout' => $layout]);
            }
        }
        return $this->render('IbtikarShareEconomyUMSBundle::form.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Reset your password',
        ));
    }
}
