<?php

namespace Ibtikar\ShareEconomyUMSBundle\Listener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser;
use Doctrine\ORM\EntityManager;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class LanguageChangeListener
{

    /* @var $securityTokenStorage \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage */
    private $securityTokenStorage;

    /* @var $acceptedLocales array */
    private $acceptedLocales;

    /* @var $defaultLocale string */
    private $defaultLocale;

    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;

    /* @var $logger \Monolog\Logger */
    private $logger;

    public function __construct(array $acceptedLocales, $defaultLocale, TokenStorage $securityTokenStorage, EntityManager $em, $logger)
    {
        $this->acceptedLocales = $acceptedLocales;
        $this->defaultLocale = $defaultLocale;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function changeRequestLocale(GetResponseEvent $event)
    {
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        $session = $request->getSession();
        $locale = $request->query->get('_locale');
        if ($locale) {
            $session->set('_locale', $locale);
        }
        $request->setLocale($session->get('_locale', $this->defaultLocale));
    }

    /**
     * @param GetResponseEvent $event
     */
    public function changeUserLocale(GetResponseEvent $event)
    {
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        $locale = $request->query->get('_locale');
        if ($locale) {
            $token = $this->securityTokenStorage->getToken();
            if ($token) {
                $user = $token->getUser();
                if (is_object($user) && $user instanceof BaseUser) {
                    if ($user->getLocale() !== $locale) {
                        $user->setLocale($locale);
                        try {
                            $this->em->flush($user);
                        } catch (\Exception $e) {
                            $this->logger->critical($e->getTraceAsString());
                        }
                    }
                }
            }
        }
    }
}
