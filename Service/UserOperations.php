<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
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

    /** @var $securityTokenStorage TokenStorage */
    private $securityTokenStorage;

    /**
     * @param string $assetsDomain
     */
    public function __construct($assetsDomain, TokenStorage $securityTokenStorage)
    {
        parent::__construct($assetsDomain);
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     * @return User|null
     */
    public function getLoggedInUser()
    {
        $token = $this->securityTokenStorage->getToken();
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
            return new JsonResponse($userData);
        }
        return $this->getInvalidCredentialsJsonResponse();
    }
}
