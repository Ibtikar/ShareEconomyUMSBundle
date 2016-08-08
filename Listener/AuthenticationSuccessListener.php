<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessLoggedInUser;
use Ibtikar\ShareEconomyUMSBundle\Service\UserOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class AuthenticationSuccessListener
{

    /** @var UserOperations $userOperations */
    private $userOperations;

    /**
     * @param UserOperations $userOperations
     */
    public function __construct(UserOperations $userOperations)
    {
        $this->userOperations = $userOperations;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $eventData = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }
        $loggedInUserResponse = new SuccessLoggedInUser();
        $responseData = $this->userOperations->getObjectDataAsArray($loggedInUserResponse);
        $responseData['user'] = array('token' => $eventData['token']);
        $userData = $this->userOperations->getUserData($user);
        foreach ($userData as $key => $value) {
            $responseData['user'][$key] = $value;
        }
        $event->setData($responseData);
    }
}
