<?php

namespace App\Tool\Notificator;

use App\Interface\Notification\VerificationCodeSender;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

class EmailNotificator //extends BaseNotificator implements VerificationCodeSender
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
//        parent::__construct($Manager, $secret);
    }

    public function sendToken(): void
    {
//        $this->generateToken();

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

    public function sendVerificationCode(string $text): void
    {
        $code = Uuid::v7();

        $email = (new Email())
            ->from($this->emailFrom)
            ->to($this->user->getEmail())
            ->subject('Код верификации на сайте')
            ->html(sprintf('<b>Для верификации используйте следующий код</b><br/><h3>%s</h3><br/><br/>
                <i>Код действует в течении 24часов</i>', $this->token));

        $conn = $this->entityManager->getConnection();
        $conn->setAutoCommit(false);
        $conn->beginTransaction();

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

    protected function getSubject(): string
    {
        return 'Сброс пароля';
    }

    protected function getHtmlMessage(): string
    {
        return sprintf('<b>Для сброса пароля вам потребуется ввести следующий код</b>
            <br/><h3>%s</h3><br/><br/>
            <i>Код действует в течении 30минут</i>', $this->token);
    }
}
