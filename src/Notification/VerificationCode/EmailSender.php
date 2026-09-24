<?php

namespace App\Notification\VerificationCode;

use App\Interface\Notification\VerificationCodeSender;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailSender implements VerificationCodeSender
{
    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function send(UserInterface $user, string $code): void
    {
        $email = (new TemplatedEmail())
            ->from(env('MAILER_RESEND_EMAIL_FROM'))
            ->to($user->getEmail())
            ->subject('Код верификации аккаунта')
            ->htmlTemplate('email/verification_code.html.twig')
            ->context(['code' => $code])
            ;

        $this->mailer->send($email);
    }
}
