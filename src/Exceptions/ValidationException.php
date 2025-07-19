<?php

namespace SemanticPen\SDK\Exceptions;

/**
 * Exception thrown when input validation fails
 */
class ValidationException extends SemanticPenException
{
    protected $field;
    protected $value;

    public function __construct(string $message = '', string $field = '', $value = null, array $details = [])
    {
        parent::__construct($message, 400, $details);
        $this->field = $field;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
    }
}