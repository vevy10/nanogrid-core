<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ApiValidationException extends UnprocessableEntityHttpException
{
    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Validation failed.'
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<int, array{field: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
