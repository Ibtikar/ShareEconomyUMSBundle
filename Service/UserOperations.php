<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\ShareEconomyToolsBundle\Service\APIOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Ibtikar\ShareEconomyUMSBundle\APIResponse;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class UserOperations extends APIOperations
{

    /** @var $container ContainerAwareInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
        parent::__construct($container->getParameter('assets_domain'));
        $this->securityTokenStorage = $container->get('security.token_storage');
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
}
