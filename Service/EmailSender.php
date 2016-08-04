<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Swift_Message;
use Swift_Attachment;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

class EmailSender
{

    public function __construct($em, $mailer, $templating, $senderEmail)
    {
        $this->em          = $em;
        $this->mailer      = $mailer;
        $this->templating  = $templating;
        $this->senderEmail = $senderEmail;
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

        $this->mailer->send($message);
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