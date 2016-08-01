<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Ibtikar\ShareEconomyUMSBundle\Entity\PhoneVerificationCode;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\Success as SuccessResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\Fail as FailResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\UserToken as UserTokenResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\RemainingTime as RemainingTimeResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\RegisterUserSuccess as RegisterUserSuccessResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\RegisterUserFail as RegisterUserFailResponse;

class UserController extends Controller
{

    /**
     * Login with an existing user
     *
     * @ApiDoc(
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
     * Edit my profile picture
     *
     * @ApiDoc(
     *  authentication=true,
     *  section="User",
     *  parameters={
     *      {"name"="file", "dataType"="string", "required"=true, "format"="{base64 encoded string}"}
     *  },
     *  statusCodes={
     *      200="Returned on success"
     *  },
     *  output="Ibtikar\ShareEconomyUMSBundle\APIResponse\User"
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function editProfilePictureAction(Request $request)
    {
        $user = $this->getUser();
        $APIOperations = $this->get('api_operations');
        $locale = $request->get('locale');
        if ($locale) {
            $APIOperations->setLocale($locale);
        }
        $tempUrlPath = null;
        $fileSystem = new Filesystem();
        $image = $request->get('file');
        if ($image) {
            $imageString = base64_decode($image);
            if ($imageString) {
                $imageRandomName = uniqid();
                $uploadDirectory = $user->getUploadRootDir() . '/api-temp/';
                $fileSystem->mkdir($uploadDirectory, 0755);
                $uploadPath = $uploadDirectory . $imageRandomName;
                if (@file_put_contents($uploadPath, $imageString)) {
                    $fileWithoutExtension = new File($uploadPath, false);
                    $imageExtension = $fileWithoutExtension->guessExtension();
                    if ($imageExtension) {
                        $tempUrlPath = "$uploadPath.$imageExtension";
                        $fileSystem->rename($uploadPath, $tempUrlPath);
                        $file = new File($tempUrlPath, false);
                        $user->setFile($file);
                    }
                }
            }
        }
        $errorsObjects = $this->get('validator')->validate($user, null, array('image', 'image-required'));
        if (count($errorsObjects) > 0) {
            if ($tempUrlPath) {
                $fileSystem->remove($tempUrlPath);
            }
            return $APIOperations->getValidationErrorsJsonResponse($errorsObjects);
        }
        try {
            $this->getDoctrine()->getManager()->flush();
            if ($tempUrlPath) {
                @unlink($tempUrlPath);
            }
            return new JsonResponse($APIOperations->getUserData($this->getUser()));
        } catch (\Exception $e) {
            return $APIOperations->getErrorResponse($e->getMessage());
        }
    }

    /**
     * Remove my profile picture
     *
     * @ApiDoc(
     *  authentication=true,
     *  section="User",
     *  output="Ibtikar\ShareEconomyUMSBundle\APIResponse\User"
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function removeProfilePictureAction()
    {
        $user = $this->getUser();
        $user->removeImage();
        $this->getDoctrine()->getManager()->flush($user);
        return new JsonResponse($this->get('api_operations')->getUserData($this->getUser()));
    }

    /**
     * Get User information by user id
     *
     * @ApiDoc(
     *  section="User",
     *  output="Ibtikar\ShareEconomyUMSBundle\APIResponse\User"
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getUserInfoAction(Request $request, $id)
    {
        $APIOperations = $this->get('api_operations');
        $locale = $request->get('locale');
        if ($locale) {
            $APIOperations->setLocale($locale);
        }
        $user = $this->getDoctrine()->getManager()->getRepository('IbtikarShareEconomyUMSBundle:User')->find($id);
        if ($user) {
            return new JsonResponse($APIOperations->getUserData($user));
        }
        return $APIOperations->getNotFoundErrorResponse();
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
     *  },
     *  statusCodes = {
     *      200 = "Returned on success",
     *      400 = "Validation failed."
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\RegisterUserSuccess",
     *      400 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\RegisterUserFail"
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
                $validationMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            $output         = new RegisterUserFailResponse();
            $output->errors = $validationMessages;
        } else {
            $phoneVerificationCode = new PhoneVerificationCode();
            $phoneVerificationCode->generateCode();

            $user->addPhoneVerificationCode($phoneVerificationCode);
            $user->generateNewEmailVerificationToken();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $output       = new RegisterUserSuccessResponse();
            $output->user = $this->get('api_operations')->getUserData($user);

            try {
                // send phone verification code
                $this->sendVerificationCodeMessage($user, $phoneVerificationCode);

                // send verification email
                $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);
            } catch (Exception $ex) {

            }
        }

        return new JsonResponse($output);
    }

    /**
     * check phone verification code validity
     *
     * @ApiDoc(
     *  description="Check phone verification code validity",
     *  section="User",
     *  parameters={
     *      {"name"="user_id", "dataType"="string", "required"=true},
     *      {"name"="code", "dataType"="string", "required"=true}
     *  },
     *  statusCodes = {
     *      200 = "Returned on success",
     *      400 = "Validation failed."
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\UserToken",
     *      400 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\Fail"
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function checkVerificationCodeAction(Request $request)
    {
        $em   = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->find($request->get('user_id'));
        $code = $em->getRepository('IbtikarShareEconomyUMSBundle:PhoneVerificationCode')->findOneBy(['user' => $request->get('user_id'), 'code' => $request->get('code')]);

        if ($code) {
            if ($code->isValid()) {
                $code->setIsVerified(true);
                $user->setIsPhoneVerified(true);

                $em->flush();

                $output        = new UserTokenResponse();
                $output->token = $this->get('lexik_jwt_authentication.encoder')->encode(['username' => $user->getUsername()]);
            } else {
                $output          = new FailResponse();
                $output->message = $this->get('translator')->trans('expired_verification_code');
            }
        } else {
            $output          = new FailResponse();
            $output->message = $this->get('translator')->trans('wrong_verification_code');
        }

        return new JsonResponse($this->get('api_operations')->getObjectDataAsArray($output));
    }

    /**
     * resend phone verification code
     *
     * @ApiDoc(
     *  description="Resend phone verification code validity",
     *  section="User",
     *  parameters={
     *      {"name"="user_id", "dataType"="string", "required"=true}
     *  },
     *  statusCodes = {
     *      200 = "Returned on success",
     *      400 = "Validation failed."
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\Success",
     *      400 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\Fail"
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function resendVerificationCodeAction(Request $request)
    {
        $em   = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->find($request->get('user_id'));

        if ($user) {
            $phoneVerificationCode = new PhoneVerificationCode();
            $phoneVerificationCode->generateCode();

            $user->addPhoneVerificationCode($phoneVerificationCode);
            $em->persist($user);

            if ($this->sendVerificationCodeMessage($user, $phoneVerificationCode)) {
                $em->flush();
                $output = new SuccessResponse();
            } else {
                $output          = new FailResponse();
                $output->message = $this->get('translator')->trans('verification_message_not_sent', [], 'validators');
            }
        } else {
            $output          = new FailResponse();
            $output->message = $this->get('translator')->trans('user_not_found', [], 'validators');
        }

        return new JsonResponse($this->get('api_operations')->getObjectDataAsArray($output));
    }

    /**
     * get verification code remaining validity time in seconds
     *
     * @ApiDoc(
     *  description="get verification code remaining validity time in seconds",
     *  section="User",
     *  parameters={
     *      {"name"="user_id", "dataType"="string", "required"=true},
     *  },
     *  statusCodes = {
     *      200 = "Returned on success",
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\RemainingTime",
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getVerificationRemainingTimeAction(Request $request)
    {
        $em              = $this->getDoctrine()->getEntityManager();
        $code            = $em->getRepository('IbtikarShareEconomyUMSBundle:PhoneVerificationCode')->findOneBy(['user' => $request->get('user_id')], ['createdAt' => 'desc']);
        $output          = new RemainingTimeResponse();
        $output->seconds = $code->getValidityRemainingSeconds();

        return new JsonResponse($this->get('api_operations')->getObjectDataAsArray($output));
    }

    /**
     * update phone number
     *
     * @ApiDoc(
     *  description="update phone number",
     *  section="User",
     *  parameters={
     *      {"name"="user_id", "dataType"="string", "required"=true},
     *      {"name"="phone", "dataType"="string", "required"=true},
     *  },
     *  statusCodes = {
     *      200 = "Returned on success",
     *      400 = "Validation failed."
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\Success",
     *      400 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\Fail"
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function updatePhoneNumberAction(Request $request)
    {
        $em   = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->find($request->get('user_id'));

        if ($user) {
            if ($user->getPhone() == $request->get('phone')) {
                $output          = new FailResponse();
                $output->message = $this->get('translator')->trans('same_phone_validation', [], 'validators');
            } else {
                $user->setPhone($request->get('phone'));
                $validator = $this->get('validator');
                $errors    = $validator->validate($user, null, ['phone']);

                if (count($errors) > 0) {
                    $output = new FailResponse();

                    foreach ($errors as $error) {
                        $output->message = $error->getMessage();
                    }
                } else {
                    $phoneVerificationCode = new PhoneVerificationCode();
                    $phoneVerificationCode->generateCode();

                    $user->addPhoneVerificationCode($phoneVerificationCode);
                    $em->persist($user);

                    if ($this->sendVerificationCodeMessage($user, $phoneVerificationCode)) {
                        $em->flush();
                        $output = new SuccessResponse();
                    } else {
                        $output          = new FailResponse();
                        $output->message = $this->get('translator')->trans('verification_message_not_sent', [], 'validators');
                    }
                }
            }
        } else {
            $output          = new FailResponse();
            $output->message = $this->get('translator')->trans('user_not_found', [], 'validators');
        }

        return new JsonResponse($this->get('api_operations')->getObjectDataAsArray($output));
    }

    /**
     *
     * @param type $user
     * @param type $code
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return boolean
     */
    private function sendVerificationCodeMessage($user, $code)
    {
        $return = false;

        try {
            $message = "Verification code for Akly is (".$code->getCode().") valid for ".PhoneVerificationCode::CODE_EXPIRY_MINUTES." minutes";
            $this->get('jhg_nexmo_sms')->sendText($user->getPhone(), $message);
            $return  = true;
        } catch (Exception $ex) {
            $return = false;
        }

        return $return;
    }
}