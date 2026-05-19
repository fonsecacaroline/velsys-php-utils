<?php

declare(strict_types=1);

namespace Core\Security;

use DateTime;
use PDO;

class RateLimiter
{
    private PDO $pdo;

    private int $maxAttempts;

    private int $windowMinutes;

    public function __construct(
        PDO $pdo,
        int $maxAttempts = 5,
        int $windowMinutes = 10
    ) {
        $this->pdo = $pdo;

        $this->maxAttempts = $maxAttempts;

        $this->windowMinutes = $windowMinutes;
    }

    public function check(string $identifier): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT
                attempts,
                last_attempt
            FROM rate_limits
            WHERE identifier = :identifier
        ");

        $stmt->execute([
            ':identifier' => $identifier
        ]);

        $row = $stmt->fetch();

        if (!$row) {
            return true;
        }

        $lastAttempt = new DateTime(
            $row['last_attempt']
        );

        $now = new DateTime();

        $diffMinutes =
            ($now->getTimestamp() - $lastAttempt->getTimestamp()) / 60;

        /**
         * Reset expired window
         */
        if ($diffMinutes > $this->windowMinutes) {

            $this->reset($identifier);

            return true;
        }

        return
            (int) $row['attempts'] < $this->maxAttempts;
    }

    public function hit(string $identifier): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits
                (
                    identifier,
                    attempts,
                    last_attempt
                )
            VALUES
                (
                    :identifier,
                    1,
                    NOW()
                )

            ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                last_attempt = NOW()
        ");

        $stmt->execute([
            ':identifier' => $identifier
        ]);
    }

    public function reset(string $identifier): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limits
            WHERE identifier = :identifier
        ");

        $stmt->execute([
            ':identifier' => $identifier
        ]);
    }
}