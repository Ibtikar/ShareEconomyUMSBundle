<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /** @var $user User|null */
    private $user;

    /**
     * @param string $assetsDomain
     */
    public function __construct($assetsDomain, TokenStorage $securityTokenStorage)
    {
        parent::__construct($assetsDomain);
        $this->securityTokenStorage = $securityTokenStorage;
        $token = $this->securityTokenStorage->getToken();
        if ($token && is_object($token)) {
            $user = $token->getUser();
            if (is_object($user) && $user instanceof User) {
                $this->user = $user;
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
     * @return JsonResponse
     */
    public function getLoggedInUserDataJsonResponse()
    {
        if ($this->user) {
            $userData = $this->getUserData($this->user);
            $userData['token'] = '';
            return new JsonResponse($userData);
        }
        return $this->getInvalidCredentialsJsonResponse();
    }
}
