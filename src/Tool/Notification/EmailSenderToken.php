<?php

namespace App\Tool\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderToken extends BaseSenderToken
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire(env: 'APP_SECRET')]
        protected string                 $secret,
        #[Autowire(env: 'MAILER_RESEND_EMAIL_FROM')]
        private readonly string          $emailFrom,
        #[Autowire(service: 'monolog.logger.senderToken')]
        protected LoggerInterface        $logger,
        EntityManagerInterface           $Manager,
    )
    {
        parent::__construct($Manager, $secret);
    }

    public function sendToken(): void
    {
        $this->generateToken();

        $email = (new Email())
            ->from($this->emailFrom)
            ->to($this->user->getEmail())
            ->subject($this->getSubject())
            ->html($this->getHtmlMessage());

        $conn = $this->entityManager->getConnection();
        $conn->setAutoCommit(false);
        $conn->beginTransaction();

        $this->save();

        try {
            $this->mailer->send($email);

            $conn->commit();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            $conn->rollback();
            throw new TransportException("Не удалось отправить письмо по почте");
        } catch (\Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
}
