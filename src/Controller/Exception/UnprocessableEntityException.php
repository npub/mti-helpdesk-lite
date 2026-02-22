<?php

declare(strict_types=1);

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityException extends BadRequestException
{
    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function __construct(string $message = 'Ошибка валидации данных', array|string|int|null $payload = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $payload, $previous);

        $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
        $this->codeString = 'unprocessable_entity';
    }
}
