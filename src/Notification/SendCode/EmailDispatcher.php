<?php

declare(strict_types=1);

namespace App\Notification\SendCode;

use App\Enum\User\Channel;
use App\Interface\Notification\SendCodeDispatcher;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailDispatcher implements SendCodeDispatcher
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'MAILER_RESEND_EMAIL_FROM')]
        private readonly string $emailFrom
    )
    {
    }

    #[\Override]
    public function send(UserInterface $user, string $code, string $nameOperation): void
    {
        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($user->getEmail())
            ->subject(sprintf('Ваш код для %s', $nameOperation))
            ->htmlTemplate('email/send_code.html.twig')
            ->context(['code' => $code, 'nameOperation' => $nameOperation]);
            ;

        $this->mailer->send($email);
    }

    public function support(Channel $type): bool
    {
        return $type === Channel::EMAIL;
    }
}
