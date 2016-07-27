<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class AuthenticationFailureListener
{

    /** @var $tranlator TranslatorInterface */
    private $translator;

    /** @var $locale string */
    private $locale;

    public function __construct(TranslatorInterface $translator, $locale)
    {
        $this->translator = $translator;
        $this->locale = $locale;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $request = $event->getRequest();
        $locale = $event->getRequest()->get('locale', $this->locale);
        $exception = $event->getException();
        $errorMessage = $exception->getMessage();
        if ($exception instanceof UsernameNotFoundException) {
            $errorMessage = 'The entered email is not registered, please enter again.';
            if ($exception->getUsername() === AuthenticationProviderInterface::USERNAME_NONE_PROVIDED) {
                $errorMessage = 'Please fill the mandatory field first.';
            }
        } else if ($exception instanceof BadCredentialsException) {
            if (!$request->get('password')) {
                $errorMessage = 'Please fill the mandatory field first.';
            }
        }
        $response = new JsonResponse(array(
            'status' => 'error',
            'code' => 401,
            'message' => $this->translator->trans($errorMessage, array(), 'security', $locale),
        ));
        $event->setResponse($response);
    }
}
