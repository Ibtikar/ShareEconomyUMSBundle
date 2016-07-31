<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class CheckAPILogin
{

    /** @var $securityTokenStorage TokenStorage */
    private $securityTokenStorage;

    /**
     * @param TokenStorage $securityTokenStorage
     */
    public function __construct(TokenStorage $securityTokenStorage)
    {
        $this->securityTokenStorage = $securityTokenStorage;
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
                    if (is_object($user) && $user instanceof User) {
                        return;
                    }
                }
                $event->setResponse(new JsonResponse(array('status' => 'error', 'code' => 401, 'message' => 'Invalid credentials')));
            }
        }
    }
}
