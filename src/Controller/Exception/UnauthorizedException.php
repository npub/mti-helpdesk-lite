<?php

declare(strict_types=1);

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends ApiHttpException
{
    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function __construct(string $message = 'Неавторизованный доступ', array|string|int|null $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 'unauthorized', $payload, Response::HTTP_UNAUTHORIZED, $previous);
    }
}
