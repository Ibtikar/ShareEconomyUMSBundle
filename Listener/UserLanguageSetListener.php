<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class UserLanguageSetListener
{

    /* @var $session \Symfony\Component\HttpFoundation\Session\Session */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        if ($token) {
            $user = $token->getUser();
            if (is_object($user) && $user instanceof BaseUser) {
                if (null !== $user->getLocale()) {
                    $this->session->set('_locale', $user->getLocale());
                }
            }
        }
    }
}
