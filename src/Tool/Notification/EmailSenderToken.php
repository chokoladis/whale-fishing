<?php

namespace App\Tool\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderToken extends BaseSenderToken
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'MAILER_RESEND_EMAIL_FROM')]
        private readonly string $emailFrom,
        EntityManagerInterface $Manager,
    )
    {
        parent::__construct($Manager);
    }

    public function sendToken()
    {
        $this->generateToken()->save();

        $email = (new Email())
            ->from($this->emailFrom)
            ->to($this->user->getEmail())
            ->subject($this->getSubject())
            ->html($this->getHtmlMessage());

        $this->mailer->send($email);
    }
}
