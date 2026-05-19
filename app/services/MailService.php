<?php

declare(strict_types=1);

namespace Core\Services;

use PDO;
use PDOException;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private PHPMailer $mailer;

    private PDO $pdo;

    public function __construct(
        array $smtpConfig,
        PDO $pdo
    ) {
        $this->pdo = $pdo;

        $this->mailer = new PHPMailer(true);

        $this->configureMailer($smtpConfig);
    }

    private function configureMailer(array $config): void
    {
        $this->mailer->isSMTP();

        $this->mailer->Host = $config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['username'];
        $this->mailer->Password = $config['password'];
        $this->mailer->Port = $config['port'];

        $this->mailer->CharSet = 'UTF-8';

        $this->mailer->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $this->mailer->setFrom(
            $config['from_email'],
            $config['from_name']
        );
    }

    public function send(array $data): bool
    {
        try {

            $this->storeMessage($data);

            $this->mailer->addAddress(
                $data['recipient_email']
            );

            $this->mailer->addReplyTo(
                $data['email'],
                $data['name']
            );

            $this->mailer->isHTML(true);

            $this->mailer->Subject =
                $data['subject'];

            $this->mailer->Body =
                $this->buildHtmlMessage($data);

            $this->mailer->AltBody =
                strip_tags($data['message']);

            return $this->mailer->send();

        } catch (MailException|PDOException $e) {

            return false;
        }
    }

    private function buildHtmlMessage(array $data): string
    {
        return "
            <strong>Name:</strong> {$data['name']}<br>
            <strong>Email:</strong> {$data['email']}<br><br>

            <p>{$data['message']}</p>
        ";
    }

    private function storeMessage(array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO messages
                (
                    name,
                    email,
                    subject,
                    message,
                    created_at
                )
            VALUES
                (
                    :name,
                    :email,
                    :subject,
                    :message,
                    NOW()
                )
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':subject' => $data['subject'],
            ':message' => $data['message']
        ]);
    }
}