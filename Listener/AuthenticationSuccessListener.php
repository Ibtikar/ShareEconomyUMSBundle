<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\SuccessLoggedInUser;
use Ibtikar\ShareEconomyUMSBundle\Service\UserOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class AuthenticationSuccessListener
{

    /** @var UserOperations $userOperations */
    private $userOperations;

    /**
     * @param ContainerAwareInterface $container
     */
    public function __construct($container)
    {
        $this->userOperations = $container->get('user_operations');
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $eventData = $event->getData();
        $user      = $event->getUser();

        if (!$user instanceof BaseUser) {
            return;
        }

        $loggedInUserResponse = new SuccessLoggedInUser();
        $responseData         = $this->userOperations->getObjectDataAsArray($loggedInUserResponse);
        $userData             = $this->userOperations->getUserData($user);

        foreach ($userData as $key => $value) {
            $responseData['user'][$key] = $value;
        }

        $responseData['user']['token'] = $eventData['token'];
        $event->setData($responseData);
    }
}
