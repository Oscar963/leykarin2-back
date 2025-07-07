<?php

namespace App\Exceptions\Import;

class RateLimitExceededException extends ImportException
{
    protected $errorCode = 'RATE_LIMIT_EXCEEDED';
    protected $httpStatus = 429;

    public function __construct(int $userId, int $retryAfter = 3600)
    {
        parent::__construct(
            'Demasiadas importaciones. Intente más tarde.',
            0,
            null,
            [
                'user_id' => $userId,
                'retry_after_seconds' => $retryAfter,
                'retry_after_human' => gmdate('H:i:s', $retryAfter)
            ]
        );
    }
} 