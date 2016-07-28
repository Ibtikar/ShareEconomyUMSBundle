<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Ibtikar\ShareEconomyUMSBundle\Entity\PhoneVerificationCode;


class UserController extends Controller
{

    /**
     * Login with an existing user
     *
     * @ApiDoc(
     *  tags={
     *      "testing"="red"
     *  },
     *  section="User",
     *  parameters={
     *      {"name"="username", "dataType"="string", "required"=true},
     *      {"name"="password", "dataType"="string", "required"=true}
     *  },
     *  statusCodes={
     *      200="Returned on success"
     *  },
     *  output="Ibtikar\ShareEconomyUMSBundle\APIResponse\User"
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function loginAction()
    {
        // The security layer will intercept this request it should never reach here
        return new JsonResponse(array('code' => 401, 'message' => 'Bad credentials'));
    }

    /**
     * Register a customer to the system
     *
     * @ApiDoc(
     *  description="Register a customer to the system",
     *  section="User",
     *  parameters={
     *      {"name"="fullName", "dataType"="string", "required"=true},
     *      {"name"="email", "dataType"="string", "required"=true},
     *      {"name"="phone", "dataType"="string", "required"=true},
     *      {"name"="userPassword", "dataType"="string", "required"=true}
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function registerCustomerAction(Request $request)
    {
        $user = new User();
        $user->setFullName($request->get('fullName'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));
        $user->setUserPassword($request->get('userPassword'));
        $user->setRoles([User::ROLE_CUSTOMER]);
        $user->setSystemUser(false);

        $validator          = $this->get('validator');
        $errors             = $validator->validate($user, null, ['signup']);
        $validationMessages = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $validationMessages[$error->getPropertyPath()] = $this->get('translator')->trans($error->getMessage(), [], 'validators');
            }

            $output = ['status' => false, 'errors' => $validationMessages];
        } else {
            $phoneVerificationCode = new PhoneVerificationCode();
            $phoneVerificationCode->generateCode();

            $user->addPhoneVerificationCode($phoneVerificationCode);
            $user->generateNewEmailVerificationToken();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $output = ['status' => true, 'user' => $user->serializeForApi()];

            try {
                // send phone verification code
                $message = "Verification code for Akly is (".$user->getPhoneVerificationCodes()->last()->getCode().") valid for ".PhoneVerificationCode::CODE_EXPIRY_MINUTES." minutes";
                $this->get('jhg_nexmo_sms')->sendText($user->getPhone(), $message);

                // send verification email
                $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);
            } catch (Exception $ex) {

            }
        }

        return new JsonResponse($output);
    }
}
