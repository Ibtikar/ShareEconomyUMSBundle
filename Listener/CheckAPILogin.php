<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Ibtikar\ShareEconomyUMSBundle\Service\UserOperations;
use Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class CheckAPILogin
{

    /* @var $securityTokenStorage \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
    private $securityTokenStorage;

    /* @var $userOperations \Ibtikar\ShareEconomyUMSBundle\Service\UserOperations */
    private $userOperations;

    /**
     * @param TokenStorage $securityTokenStorage
     * @param UserOperations $userOperations
     */
    public function __construct(TokenStorage $securityTokenStorage, UserOperations $userOperations)
    {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->userOperations = $userOperations;
    }

    /**
     * @param GetResponseEvent $event
     * @return null
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getRequestUri(), '/api') !== false) {
            if (strpos($request->getRequestUri(), '/auth') !== false || (strpos($request->getRequestUri(), '/api/doc/') === false && $request->headers->has('Authorization'))) {
                $token = $this->securityTokenStorage->getToken();
                if ($token && $token instanceof JWTUserToken) {
                    $user = $token->getUser();
                    if (is_object($user) && $user instanceof BaseUser) {
                        return;
                    }
                }
                $event->setResponse($this->userOperations->getInvalidCredentialsJsonResponse());
            }
        }
    }
}
