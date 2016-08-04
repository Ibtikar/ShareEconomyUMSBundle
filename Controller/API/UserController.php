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
     *      200="Returned on success",
     *      401="Returned if the login information was not correct",
     *      403="Returned if the api key is not valid"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\LoggedInUser",
     *      401="Ibtikar\ShareEconomyUMSBundle\APIResponse\InvalidCredentials",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey"
     *  }
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
     *      200="Returned on success",
     *      401="Returned if the authorization header is missing or expired",
     *      403="Returned if the api key is not valid",
     *      422="Returned if there is a validation error in the sent data",
     *      500="Returned if there is an internal server error"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\LoggedInUser",
     *      401="Ibtikar\ShareEconomyUMSBundle\APIResponse\InvalidCredentials",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey",
     *      422="Ibtikar\ShareEconomyToolsBundle\APIResponse\ValidationErrors",
     *      500="Ibtikar\ShareEconomyToolsBundle\APIResponse\InternalServerError"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function editProfilePictureAction(Request $request)
    {
        $user = $this->getUser();
        $userOperations = $this->get('user_operations');
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
            return $userOperations->getValidationErrorsJsonResponse($errorsObjects);
        }
        try {
            $this->getDoctrine()->getManager()->flush();
            if ($tempUrlPath) {
                @unlink($tempUrlPath);
            }
            return $userOperations->getLoggedInUserDataJsonResponse($request);
        } catch (\Exception $e) {
            return $userOperations->getErrorJsonResponse($e->getMessage());
        }
    }

    /**
     * Remove my profile picture
     *
     * @ApiDoc(
     *  authentication=true,
     *  section="User",
     *  statusCodes={
     *      200="Returned on success",
     *      401="Returned if the authorization header is missing or expired",
     *      403="Returned if the api key is not valid"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\LoggedInUser",
     *      401="Ibtikar\ShareEconomyUMSBundle\APIResponse\InvalidCredentials",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param Request $request
     * @return JsonResponse
     */
    public function removeProfilePictureAction(Request $request)
    {
        $user = $this->getUser();
        $user->removeImage();
        $this->getDoctrine()->getManager()->flush($user);
        return $this->get('user_operations')->getLoggedInUserDataJsonResponse($request);
    }

    /**
     * Get User information by user id
     *
     * @ApiDoc(
     *  section="User",
     *  statusCodes={
     *      200="Returned on success",
     *      403="Returned if the api key is not valid"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\User",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getUserInfoAction(Request $request, $id)
    {
        $userOperations = $this->get('user_operations');
        $user = $this->getDoctrine()->getManager()->getRepository('IbtikarShareEconomyUMSBundle:User')->find($id);
        if ($user) {
            return new JsonResponse($userOperations->getUserData($user));
        }
        return $userOperations->getNotFoundErrorJsonResponse();
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
            $output->user = $this->get('user_operations')->getUserData($user);

            // send phone verification code
            $this->sendVerificationCodeMessage($user, $phoneVerificationCode);

            // send verification email
            $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);
        }

        return new JsonResponse($output);
    }

    /**
     * Update user information
     *
     * @ApiDoc(
     *  authentication=true,
     *  description="Update user information",
     *  section="User",
     *  parameters={
     *      {"name"="fullName", "dataType"="string", "required"=true},
     *      {"name"="email", "dataType"="string", "required"=true},
     *      {"name"="phone", "dataType"="string", "required"=true},
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
    public function updateUserInfoAction(Request $request)
    {
        $user = $this->getUser();

        $oldEmail = $user->getEmail();
        $oldPhone = $user->getPhone();

        $user->setFullName($request->get('fullName'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));

        $validator          = $this->get('validator');
        $errors             = $validator->validate($user, null, ['edit']);
        $validationMessages = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $validationMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            $output         = new RegisterUserFailResponse();
            $output->errors = $validationMessages;
        } else {
            if ($user->getEmail() !== $oldEmail) {
                $user->generateNewEmailVerificationToken();
                $user->setEmailVerified(false);

                // send verification email
                $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);
            }

            if ($user->getPhone() !== $oldPhone) {
                $phoneVerificationCode = new PhoneVerificationCode();
                $phoneVerificationCode->generateCode();

                $user->addPhoneVerificationCode($phoneVerificationCode);

                // send phone verification code
                $this->sendVerificationCodeMessage($user, $phoneVerificationCode);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $output       = new RegisterUserSuccessResponse();
            $output->user = $this->get('user_operations')->getUserData($user);
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

        return $this->get('api_operations')->getJsonResponseForObject($output);
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

        return $this->get('api_operations')->getJsonResponseForObject($output);
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

        return $this->get('api_operations')->getJsonResponseForObject($output);
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

        return $this->get('api_operations')->getJsonResponseForObject($output);
    }

    /**
     * check if email verified or not
     *
     * @ApiDoc(
     *  description="check if email verified or not",
     *  section="User",
     *  parameters={
     *      {"name"="user_id", "dataType"="string", "required"=true}
     *  },
     *  statusCodes = {
     *      200 = "Returned if emial verified",
     *      400 = "Returned if email not verified yet."
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
    public function isEmailVerifiedAction(Request $request)
    {
        $em     = $this->getDoctrine()->getEntityManager();
        $user   = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->find($request->get('user_id'));
        $output = $user->getEmailVerified() ? new SuccessResponse() : new FailResponse();

        return $this->get('api_operations')->getJsonResponseForObject($output);
    }

    /**
     * change user password
     *
     * @ApiDoc(
     *  authentication=true,
     *  description="change user password",
     *  section="User",
     *  parameters={
     *      {"name"="oldPassword", "dataType"="string", "required"=true},
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
    public function changePasswordAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->find($currentUser->getId());

        $user->setOldPassword($request->get('oldPassword'));
        $user->setUserPassword($request->get('userPassword'));

        $validator          = $this->get('validator');
        $errors             = $validator->validate($user, null, ['changePassword']);
        $validationMessages = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $validationMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            $output         = new RegisterUserFailResponse();
            $output->errors = $validationMessages;
        } else {
            $user->setValidPassword();
            $em->flush();

            $output       = new RegisterUserSuccessResponse();
            $output->user = $this->get('user_operations')->getUserData($user);
        }

        return new JsonResponse($output);
    }

    /**
     * send forgot password email
     *
     * @ApiDoc(
     *  description="send forgot password email",
     *  section="User",
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true}
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
    public function sendResetPasswordEmailAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->findOneBy(['email' => $request->get('email')]);

        if (!$user) {
            $output          = new FailResponse();
            $output->message = $this->get('translator')->trans('email_not_registered');
        } else {
            if (!$user->canRequestForgetPasswordEmail()) {
                $output          = new FailResponse();
                $output->message = $this->get('translator')->trans('reach_max_forget_password_requests_error');
            } else {
                $user->generateNewForgetPasswordToken();
                $em->flush();
                $this->get('ibtikar.shareeconomy.ums.email_sender')->sendResetPasswordEmail($user);

                $output = new SuccessResponse();
            }
        }

        return new JsonResponse($output);
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
        } catch (\Exception $ex) {
            $return = false;
        }

        return $return;
    }
}