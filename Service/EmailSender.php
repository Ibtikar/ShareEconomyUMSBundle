<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Swift_Message;
use Swift_Attachment;
use Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser;
use Psr\Log\LoggerInterface;

class EmailSender
{

    protected $em;
    protected $mailer;
    protected $templating;
    protected $senderEmail;
    protected $logger;
    protected $translator;

    public function __construct($em, $mailer, $templating, $senderEmail, LoggerInterface $logger, $translator)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->senderEmail = $senderEmail;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function send($to = "", $subject = "", $content = "", $type = "text/html", $files = array())
    {
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->senderEmail, $this->senderEmail)
                ->setSender($this->senderEmail, $this->senderEmail)
                ->setTo($to)
                ->setContentType($type)
                ->setBody($content);

        if (!empty($files)) {
            foreach ($files as $name => $path) {
                $message->attach(Swift_Attachment::fromPath($path)->setFilename($name));
            }
        }

        try {
            $this->mailer->send($message);
        } catch (\Exception $exc) {
            $this->logger->critical($exc->getTraceAsString());
        }
    }

    public function sendToMany($to = array(), $subject = "", $content = "", $type = "text/html", $files = array())
    {
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->senderEmail, $this->senderEmail)
                ->setSender($this->senderEmail, $this->senderEmail)
                ->setTo($this->senderEmail, $this->senderEmail)
                ->setBcc($to)
                ->setContentType($type)
                ->setBody($content);

        if (!empty($files)) {
            foreach ($files as $name => $path) {
                $message->attach(Swift_Attachment::fromPath($path)->setFilename($name));
            }
        }

        try {
            $this->mailer->send($message);
        } catch (\Exception $exc) {
            $this->logger->critical($exc->getTraceAsString());
        }
    }

    /**
     * send verification email
     *
     * @param BaseUser $user
     * @return type
     */
    public function sendEmailVerification(BaseUser $user)
    {
        return $this->send($user->getEmail(), $this->translator->trans('Please verify your email', array(), 'email'), $this->templating->render('IbtikarShareEconomyUMSBundle:Emails:emailVerification.html.twig', ['user' => $user]));
    }

    /**
     * send forget password email
     *
     * @param BaseUser $user
     * @return type
     */
    public function sendResetPasswordEmail(BaseUser $user)
    {
        return $this->send($user->getEmail(), $this->translator->trans('Reset password', array(), 'email'), $this->templating->render('IbtikarShareEconomyUMSBundle:Emails:sendResetPasswordEmail.html.twig', ['user' => $user]));
    }
}
