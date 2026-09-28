<?php

declare(strict_types=1);

namespace App\Notification\VerificationCode;

use App\Enum\User\TypeVerify;
use App\Interface\Notification\VerificationCodeSender;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailSender implements VerificationCodeSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'MAILER_RESEND_EMAIL_FROM')]
        private readonly string $emailFrom
    )
    {
    }

    public function send(UserInterface $user, string $code): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($user->getEmail())
            ->subject('Код верификации аккаунта')
            ->htmlTemplate('email/verification_code.html.twig')
            ->context(['code' => $code])
            ;

        $this->mailer->send($email);
    }

    public function support(TypeVerify $type): bool
    {
        return $type === TypeVerify::EMAIL;
    }
}
