<?php

declare(strict_types=1);

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends ApiHttpException
{
    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function __construct(string $message = 'Объект не найден', array|string|int|null $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 'not_found', $payload, Response::HTTP_NOT_FOUND, $previous);
    }
}
