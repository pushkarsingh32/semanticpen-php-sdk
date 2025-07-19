<?php

namespace SemanticPen\SDK\Exceptions;

use Exception;

/**
 * Base exception class for all SemanticPen SDK exceptions
 */
class SemanticPenException extends Exception
{
    protected $statusCode;
    protected $details;

    public function __construct(string $message = '', int $statusCode = 0, array $details = [], Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}