<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\ShareEconomyToolsBundle\Service\APIOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Ibtikar\ShareEconomyUMSBundle\Entity\PhoneVerificationCode;
use Ibtikar\ShareEconomyUMSBundle\APIResponse;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class UserOperations extends APIOperations
{

    /** @var $container ContainerAwareInterface */
    private $container;

    const MAX_DAILY_VERIFICATION_CODE_REQUESTS = 5;

    /**
     * @param ContainerAwareInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        parent::__construct($container->getParameter('assets_domain'));
    }

    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Gets a container configuration parameter by its name.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @return User|null
     */
    public function getLoggedInUser()
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token && is_object($token)) {
            $user = $token->getUser();
            if (is_object($user) && $user instanceof User) {
                return $user;
            }
        }
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function getInvalidCredentialsJsonResponse($message = null)
    {
        $errorResponse = new APIResponse\InvalidCredentials();
        if ($message) {
            $errorResponse->message = $message;
        }
        return $this->getJsonResponseForObject($errorResponse);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserData(User $user)
    {
        $responseUser = new APIResponse\User();
        $responseUser->id = $user->getId();
        $responseUser->fullName = $user->getFullName();
        $responseUser->email = $user->getEmail();
        $responseUser->phone = $user->getPhone();
        $responseUser->emailVerified = $user->getEmailVerified();
        $responseUser->isPhoneVerified = $user->getIsPhoneVerified();
        if ($user->getImage()) {
            $responseUser->image = $this->assetsDomain . '/' . $user->getWebPath();
        }
        return $this->getObjectDataAsArray($responseUser);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoggedInUserDataJsonResponse(Request $request)
    {
        $loggedInUser = $this->getLoggedInUser();
        if ($loggedInUser) {
            $userData = $this->getUserData($loggedInUser);
            $authorizationHeader = $request->headers->get('Authorization');
            if ($authorizationHeader) {
                $userData['token'] = str_replace('Bearer ', '', $authorizationHeader);
            }
            $loggedInUserResponse = new APIResponse\SuccessLoggedInUser();
            $loggedInUserResponse->user = $userData;
            return $this->getJsonResponseForObject($loggedInUserResponse);
        }
        return $this->getInvalidCredentialsJsonResponse();
    }

    /**
     * @param string $userEmail
     * @return string
     */
    public function sendResetPasswordEmail($userEmail)
    {
        $translator = $this->get('translator');
        if (!$userEmail) {
            return $translator->trans('fill_mandatory_field', array(), 'validators');
        }
        $em = $this->get('doctrine')->getManager();
        $user = $em->getRepository('IbtikarShareEconomyUMSBundle:User')->findOneBy(['email' => $userEmail]);
        if (!$user) {
            return $translator->trans('email_not_registered');
        }
        if (!$user->canRequestForgetPasswordEmail()) {
            return $translator->trans('reach_max_forget_password_requests_error');
        }
        $user->generateNewForgetPasswordToken();
        $em->flush($user);
        $this->get('ibtikar.shareeconomy.ums.email_sender')->sendResetPasswordEmail($user);
        return 'success';
    }

    /**
     * add new verification code to the user
     *
     * @param User $user
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return PhoneVerificationCode
     */
    public function addNewVerificationCode(User $user)
    {
        $phoneVerificationCode = new PhoneVerificationCode();
        $phoneVerificationCode->generateCode();

        $user->addPhoneVerificationCode($phoneVerificationCode);

        return $phoneVerificationCode;
    }

    /**
     * validate object and return error messages array
     *
     * @param type $object
     * @param type $groups
     * @return array
     */
    public function validateObject($object, $groups)
    {
        $validationMessages = [];
        $errors             = $this->get('validator')->validate($object, null, $groups);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $validationMessages[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        return $validationMessages;
    }

    /**
     * send phone verification code SMS
     *
     * @param type $user
     * @param type $code
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return boolean
     */
    public function sendVerificationCodeMessage(User $user, $code)
    {
        try {
            $message = "Verification code for Akly is (".$code->getCode().") valid for ".PhoneVerificationCode::CODE_EXPIRY_MINUTES." minutes";
            $this->get('jhg_nexmo_sms')->sendText($user->getPhone(), $message);
            $return  = true;
        } catch (\Exception $ex) {
            $return = false;
        }

        return $return;
    }

    /**
     * check if the user reached the max todays requests
     *
     * @param User $user
     * @return boolean
     */
    public function canRequestPhoneVerificationCode(User $user)
    {
        $em         = $this->get('doctrine')->getManager();
        $codesCount = $em->getRepository('IbtikarShareEconomyUMSBundle:PhoneVerificationCode')->countTodaysCodes($user);

        return $codesCount < self::MAX_DAILY_VERIFICATION_CODE_REQUESTS;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @param User $user
     * @return boolean
     */
    public function verifyUserEmail(User $user)
    {
        $user->setEmailVerified(true);
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiryTime(null);
        $this->get('doctrine')->getManager()->flush($user);
        return true;
    }
}
