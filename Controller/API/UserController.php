<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ibtikar\ShareEconomyUMSBundle\APIResponse as UMSApiResponse;
use AppBundle\Entity\User;

class UserController extends Controller
{
    /**
     *
     * @var Ibtikar\ShareEconomyUMSBundle\Service\UserOperations
     */
    public $userOperations;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->userOperations = $this->get('user_operations');
    }

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
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessLoggedInUser",
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
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessLoggedInUser",
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
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessLoggedInUser",
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
     *      200="Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessUser",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getUserInfoAction(Request $request, $id)
    {
        $userOperations = $this->get('user_operations');
        $user = $this->getDoctrine()->getManager()->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);
        if ($user) {
            $data = $userOperations->getObjectDataAsArray(new UMSApiResponse\SuccessUser());
            $data['user'] = $userOperations->getUserData($user);
            return new JsonResponse($data);
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

        $validationMessages = $this->userOperations->validateObject($user, ['signup']);

        if (count($validationMessages)) {
            $output         = new UMSApiResponse\RegisterUserFail();
            $output->errors = $validationMessages;
        } else {
            $phoneVerificationCode = $this->userOperations->addNewVerificationCode($user);
            $this->userOperations->generateNewEmailVerificationToken($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            try {
                $em->flush();

                $output       = new UMSApiResponse\RegisterUserSuccess();
                $output->user = $this->get('user_operations')->getUserData($user);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $exc) {
                return $this->registerCustomerAction($request);
            } catch (\Exception $exc) {
                $output          = new UMSApiResponse\Fail();
                $output->message = $this->get('translator')->trans("something_went_wrong");
            }

            if ($output->status) {
                try {
                    // send phone verification code
                    $this->userOperations->sendVerificationCodeMessage($user, $phoneVerificationCode);

                    // send verification email
                    $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);
                } catch (\Exception $exc) {
                    $this->get('logger')->error($exc->getMessage());
                }
            }
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
        $user           = $this->getUser();
        $oldEmail       = $user->getEmail();
        $oldPhone       = $user->getPhone();

        $user->setFullName($request->get('fullName'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));

        $validationMessages = $this->userOperations->validateObject($user, ['edit']);

        if (count($validationMessages)) {
            $output         = new UMSApiResponse\RegisterUserFail();
            $output->errors = $validationMessages;
        } else {
            $this->userOperations->updateUserInformation($user, $oldEmail, $oldPhone);
            $output       = new UMSApiResponse\RegisterUserSuccess();
            $output->user = $this->get('user_operations')->getUserData($user);
            $output->user['token'] = $this->get('lexik_jwt_authentication.encoder')->encode(['username' => $user->getUsername()]);
        }

        return new JsonResponse($output);
    }

    /**
     * check phone verification code validity
     *
     * @ApiDoc(
     *  description="Check phone verification code validity",
     *  section="User",
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
    public function checkVerificationCodeAction(Request $request, $id, $code)
    {
        $em               = $this->getDoctrine()->getManager();
        $user             = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);
        $verificationCode = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->getPhoneVerificationCode($id, $code);

        if ($verificationCode) {
            if ($this->get('phone_verification_code_business')->isValidCode($verificationCode)) {
                $verificationCode->setIsVerified(true);
                $user->setIsPhoneVerified(true);

                $em->flush();

                $output        = new UMSApiResponse\UserToken();
                $output->token = $this->get('lexik_jwt_authentication.encoder')->encode(['username' => $user->getUsername()]);
            } else {
                $output          = new UMSApiResponse\Fail();
                $output->message = $this->get('translator')->trans('expired_verification_code');
            }
        } else {
            $output          = new UMSApiResponse\Fail();
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
    public function resendVerificationCodeAction(Request $request, $id)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);

        if ($user) {
            if ($user->getIsPhoneVerified()){
                $output          = new UMSApiResponse\Fail();
                $output->message = $this->get('translator')->trans('phone_already_verified');
            } elseif (!$this->userOperations->canRequestPhoneVerificationCode($user)){
                $output          = new UMSApiResponse\Fail();
                $output->message = $this->get('translator')->trans('reach_max_phone_verification_requests_error');
            } else {
                $phoneVerificationCode = $this->userOperations->addNewVerificationCode($user);
                $em->persist($user);

                if ($this->userOperations->sendVerificationCodeMessage($user, $phoneVerificationCode)) {
                    $em->flush();
                    $output = new UMSApiResponse\Success();
                } else {
                    $output          = new UMSApiResponse\Fail();
                    $output->message = $this->get('translator')->trans('verification_message_not_sent', [], 'validators');
                }
            }
        } else {
            $output          = new UMSApiResponse\Fail();
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
     *  statusCodes = {
     *      200 = "Returned on success",
     *      404="Returned if the page was not found"
     *  },
     *  responseMap = {
     *      200 = "Ibtikar\ShareEconomyUMSBundle\APIResponse\RemainingTime",
     *      404="Ibtikar\ShareEconomyToolsBundle\APIResponse\NotFound"
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getVerificationRemainingTimeAction(Request $request, $id)
    {
        /* @var $user \Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser */
        $user = $this->getDoctrine()->getManager()->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);
        if (!$user) {
            return $this->get('api_operations')->getNotFoundErrorJsonResponse();
        }
        $code = $user->getPhoneVerificationCodes()->first();
        if (!$code) {
            return $this->get('api_operations')->getNotFoundErrorJsonResponse();
        }
        $output          = new UMSApiResponse\RemainingTime();
        $output->seconds = $this->get('phone_verification_code_business')->getValidityRemainingSeconds($code);
        return $this->get('api_operations')->getJsonResponseForObject($output);
    }

    /**
     * get last phone verification code
     *
     * @ApiDoc(
     *  description="get last phone verification code",
     *  section="User",
     *  statusCodes = {
     *      200 = "Returned on success",
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function getLastPhoneVerificationCodeAction(Request $request, $id)
    {
        /* @var $user \Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser */
        $user = $this->getDoctrine()->getManager()->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);
        if (!$user) {
            return $this->get('api_operations')->getNotFoundErrorJsonResponse();
        }
        $code = $user->getPhoneVerificationCodes()->first();
        if (!$code) {
            return $this->get('api_operations')->getNotFoundErrorJsonResponse();
        }
        return new JsonResponse(['code' => $code->getCode()]);
    }

    /**
     * update phone number
     *
     * @ApiDoc(
     *  description="update phone number",
     *  section="User",
     *  parameters={
     *      {"name"="phone", "dataType"="string", "required"=true}
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
    public function updatePhoneNumberAction(Request $request, $id)
    {
        $em             = $this->getDoctrine()->getManager();
        $user           = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);

        if ($user) {
            if ($user->getPhone() == $request->get('phone')) {
                $output          = new UMSApiResponse\Fail();
                $output->message = $this->get('translator')->trans('same_phone_validation', [], 'validators');
            } else {
                $user->setPhone($request->get('phone'));
                $validationMessages = $this->userOperations->validateObject($user, ['phone']);

                if (count($validationMessages)) {
                    $output = new UMSApiResponse\Fail();
                    $output->message = $validationMessages['phone'];
                } else {
                    $phoneVerificationCode = $this->userOperations->addNewVerificationCode($user);
                    $em->persist($user);

                    if ($this->userOperations->sendVerificationCodeMessage($user, $phoneVerificationCode)) {
                        $em->flush();
                        $output = new UMSApiResponse\Success();
                    } else {
                        $output          = new UMSApiResponse\Fail();
                        $output->message = $this->get('translator')->trans('verification_message_not_sent', [], 'validators');
                    }
                }
            }
        } else {
            $output          = new UMSApiResponse\Fail();
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
    public function isEmailVerifiedAction(Request $request, $id)
    {
        $em     = $this->getDoctrine()->getManager();
        $user   = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($id);
        $output = $user->getEmailVerified() ? new UMSApiResponse\Success() : new UMSApiResponse\Fail();

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
        $user = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->find($currentUser->getId());

        $user->setOldPassword($request->get('oldPassword'));
        $user->setUserPassword($request->get('userPassword'));

        $validationMessages = $this->userOperations->validateObject($user, ['changePassword']);

        if (count($validationMessages)) {
            $output         = new UMSApiResponse\RegisterUserFail();
            $output->errors = $validationMessages;
        } else {
            $user->setValidPassword();
            $em->flush();

            $output              = new UMSApiResponse\RegisterUserSuccess();
            $output->user        = $this->get('user_operations')->getUserData($user);
            $output->user['token'] = $this->get('lexik_jwt_authentication.encoder')->encode(['username' => $user->getUsername()]);
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
     *  statusCodes={
     *      200="Returned on success",
     *      403="Returned if the api key is not valid",
     *      422="Returned if there is a validation error in the sent data"
     *  },
     *  responseMap = {
     *      200="Ibtikar\ShareEconomyToolsBundle\APIResponse\Success",
     *      403="Ibtikar\ShareEconomyToolsBundle\APIResponse\InvalidAPIKey",
     *      422="Ibtikar\ShareEconomyToolsBundle\APIResponse\Fail"
     *  }
     * )
     *
     * @param Request $request
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function sendResetPasswordEmailAction(Request $request)
    {
        $message = $this->userOperations->sendResetPasswordEmail($request->get('email'));
        if ($message === 'success') {
            return $this->userOperations->getSuccessJsonResponse();
        }
        return $this->userOperations->getSingleErrorJsonResponse($message);
    }

    /**
     * resend verification email
     *
     * @ApiDoc(
     *  description="Resend verification email",
     *  section="User",
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true}
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
    public function resendVerificationEmailAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->getParameter('ibtikar_share_economy_ums.user_class'))->findOneBy(['email' => $request->get('email')]);

        if (!$user) {
            $output          = new UMSApiResponse\Fail();
            $output->message = $this->get('translator')->trans('user_not_found', [], 'validators');
        } else if ($user->getEmailVerified()) {
            $output          = new UMSApiResponse\Fail();
            $output->message = $this->get('translator')->trans('user_already_verified');
        } else if (!$this->userOperations->canRequestVerificationEmail($user)) {
            $output          = new UMSApiResponse\Fail();
            $output->message = $this->get('translator')->trans('reach_max_verification_email_requests_error');
        } else {
            $this->userOperations->generateNewEmailVerificationToken($user);
            $em->flush();

            // send verification email
            $this->get('ibtikar.shareeconomy.ums.email_sender')->sendEmailVerification($user);

            $output = new UMSApiResponse\Success();
        }

        return $this->get('api_operations')->getJsonResponseForObject($output);
    }
}