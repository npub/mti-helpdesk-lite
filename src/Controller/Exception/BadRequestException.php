<?php

declare(strict_types=1);

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends ApiHttpException
{
    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function __construct(string $message = 'Запрос содержит неверные данные', array|string|int|null $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 'bad_request', $payload, Response::HTTP_BAD_REQUEST, $previous);
    }
}
