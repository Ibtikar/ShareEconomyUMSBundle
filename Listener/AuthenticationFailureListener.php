<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Ibtikar\ShareEconomyUMSBundle\Service\UserOperations;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class AuthenticationFailureListener
{

    /** @var $tranlator TranslatorInterface */
    private $translator;

    /** @var UserOperations $userOperations */
    private $userOperations;

    /**
     * @param TranslatorInterface $translator
     * @param UserOperations $userOperations
     */
    public function __construct(TranslatorInterface $translator, UserOperations $userOperations)
    {
        $this->translator = $translator;
        $this->userOperations = $userOperations;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();
        $errorMessage = $exception->getMessage();
        if (!$request->get('password')) {
            $errorMessage = 'Please fill the mandatory field first.';
        } else {
            if ($exception instanceof UsernameNotFoundException) {
                $errorMessage = 'The entered email is not registered, please enter again.';
                if ($exception->getUsername() === AuthenticationProviderInterface::USERNAME_NONE_PROVIDED) {
                    $errorMessage = 'Please fill the mandatory field first.';
                }
            }
        }
        $event->setResponse($this->userOperations->getInvalidCredentialsJsonResponse($this->translator->trans($errorMessage, array(), 'security')));
    }
}
