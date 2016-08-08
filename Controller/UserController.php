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
        $em   = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->findOneBy(['email' => $request->get('email'), 'emailVerificationToken' => $request->get('token')]);

        if (!$user) {
            return $this->createNotFoundException();
        }

        $now = new \DateTime();

        if ($user->getEmailVerificationTokenExpiryTime() < $now) {
            $this->addFlash('error', 'انتهت صلاحية استخدام هذا الرابط, من فضلك قم بطلب رابط تأكيد جديد.');
        } else {
            $user->setEmailVerified(true);
            $user->setEmailVerificationToken(null);
            $user->setEmailVerificationTokenExpiryTime(null);
            $em->flush();

            $this->addFlash('success', 'تم تفعيل البريد الإلكتروني بنجاح.');
        }

        $layout = $this->getParameter('ibtikar_share_economy_ums.frontend_layout');

        return $this->render('IbtikarShareEconomyUMSBundle:User:message.html.twig', ['layout' => $layout]);
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
