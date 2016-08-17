<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Swift_Message;
use Swift_Attachment;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Psr\Log\LoggerInterface;

class EmailSender
{
    private $em;
    private $mailer;
    private $templating;
    private $senderEmail;
    private $logger;

    public function __construct($em, $mailer, $templating, $senderEmail, LoggerInterface $logger)
    {
        $this->em          = $em;
        $this->mailer      = $mailer;
        $this->templating  = $templating;
        $this->senderEmail = $senderEmail;
        $this->logger      = $logger;
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
            $this->logger->error($exc->getTraceAsString());
        }
    }

    /**
     * send verification email
     *
     * @param User $user
     * @return type
     */
    public function sendEmailVerification(User $user)
    {
        return $this->send($user->getEmail(), 'Please verify your email', $this->templating->render('IbtikarShareEconomyUMSBundle:Emails:emailVerification.html.twig', ['user' => $user]));
    }

    /**
     * send forget password email
     *
     * @param User $user
     * @return type
     */
    public function sendResetPasswordEmail(User $user)
    {
        return $this->send($user->getEmail(), 'Reset password', $this->templating->render('IbtikarShareEconomyUMSBundle:Emails:sendResetPasswordEmail.html.twig', ['user' => $user]));
    }
}