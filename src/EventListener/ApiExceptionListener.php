<?php

namespace App\EventListener;

use App\Exception\ApiValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        $statusCode = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        if ($exception instanceof ApiValidationException) {
            $event->setResponse(new JsonResponse([
                'errors' => $exception->getErrors(),
            ], $statusCode));

            return;
        }

        $message = match ($statusCode) {
            404 => 'Resource not found.',
            500 => 'Internal server error.',
            default => $exception->getMessage() !== '' ? $exception->getMessage() : 'Request failed.',
        };

        $event->setResponse(new JsonResponse([
            'error' => [
                'status' => $statusCode,
                'message' => $message,
            ],
        ], $statusCode));
    }
}
