<?php

namespace App\Service\Api;

use App\Exception\ApiValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationService
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    public function validate(object $resource): void
    {
        $errors = $this->validator->validate($resource);
        if (count($errors) === 0) {
            return;
        }

        $violations = [];
        foreach ($errors as $error) {
            $violations[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        throw new ApiValidationException($violations);
    }
}
