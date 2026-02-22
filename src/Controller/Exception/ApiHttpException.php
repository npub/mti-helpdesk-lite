<?php

declare(strict_types=1);

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API для работы с заявками.
 */
class ApiHttpException extends \Exception
{
    /**
     * Строковый код ошибки.
     */
    protected string $codeString;

    /**
     * Дополнительные данные ошибки (опционально).
     *
     * @var array<string|int, mixed>|string|int|null
     */
    protected array|string|int|null $payload = null;

    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function __construct(string $message, string $codeString, array|string|int|null $payload = null, int $codeHttp = Response::HTTP_INTERNAL_SERVER_ERROR, ?\Throwable $previous = null)
    {
        parent::__construct($message, $codeHttp, $previous);

        $this->codeString = $codeString;
        $this->payload = $payload;
    }

    public function getCodeString(): string
    {
        return $this->codeString;
    }

    public function setCodeString(string $codeString): static
    {
        $this->codeString = $codeString;

        return $this;
    }

    /**
     * @return array<string|int, mixed>|string|int|null
     */
    public function getPayload(): array|string|int|null
    {
        return $this->payload;
    }

    /**
     * @param array<string|int, mixed>|string|int|null $payload
     */
    public function setPayload(array|string|int|null $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function createJsonResponse(): JsonResponse
    {
        $error = [
            'code' => $this->codeString,
            'message' => $this->message,
        ];

        if (null !== $this->payload) {
            $error['details'] = $this->payload;
        }

        /** @var int $code */
        $code = $this->code;

        return new JsonResponse($error, $code);
    }

    public static function createUnknownErrorJsonResponse(\Throwable $th): JsonResponse
    {
        $exception = new self($th->getMessage(), 'internal_error', null, Response::HTTP_INTERNAL_SERVER_ERROR, $th);

        return $exception->createJsonResponse();
    }
}
