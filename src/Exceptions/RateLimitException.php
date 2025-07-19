<?php

namespace SemanticPen\SDK\Exceptions;

/**
 * Exception thrown when API rate limits are exceeded
 */
class RateLimitException extends SemanticPenException
{
    protected $retryAfter;

    public function __construct(string $message = '', int $retryAfter = 0, array $details = [])
    {
        parent::__construct($message, 429, $details);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}