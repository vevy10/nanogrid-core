<?php

namespace App\Service\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class JsonRequestDecoder
{
    /**
     * @return array<string, mixed>
     */
    public function decode(Request $request): array
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        return $payload;
    }
}
