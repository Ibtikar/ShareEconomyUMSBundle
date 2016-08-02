<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Ibtikar\ShareEconomyToolsBundle\Service\APIOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class AuthenticationSuccessListener
{

    /** @var APIOperations $APIOperations */
    private $APIOperations;

    /**
     * @param APIOperations $APIOperations
     */
    public function __construct(APIOperations $APIOperations)
    {
        $this->APIOperations = $APIOperations;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }
        $userData = $this->APIOperations->getUserData($user);
        foreach ($userData as $key => $value) {
            $data[$key] = $value;
        }
        $event->setData($data);
    }
}
