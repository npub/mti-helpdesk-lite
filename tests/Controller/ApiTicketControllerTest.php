<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Status;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class ApiTicketControllerTest extends WebTestCase
{
    private const API_KEY = 'test-api-key1';

    protected function setUp(): void
    {
        parent::setUp();

        // Установка переменной окружения для тестов
        $_ENV['APP_API_KEY'] = self::API_KEY;
    }

    /**
     * Тест создания заявки.
     */
    public function testCreateTicket(): void
    {
        $client = self::createClient();

        $data = [
            'title' => 'Тестовая заявка',
            'description' => 'Описание тестовой заявки',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('id', $content);
        self::assertIsInt($content['id']);
    }

    /**
     * Тест создания заявки с неверным API ключом.
     */
    public function testCreateTicketWithInvalidApiKey(): void
    {
        $client = self::createClient();

        $data = [
            'title' => 'Тестовая заявка',
            'description' => 'Описание тестовой заявки',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => 'invalid-key',
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(401);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('code', $content);
        self::assertSame('unauthorized', $content['code']);
    }

    /**
     * Тест создания заявки с невалидными данными.
     */
    public function testCreateTicketWithInvalidData(): void
    {
        $client = self::createClient();

        $data = [
            'title' => 'Тестовая заявка',
            // Отсутствует description и author_email
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(422);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('code', $content);
        self::assertSame('unprocessable_entity', $content['code']);
    }

    /**
     * Тест получения заявки.
     */
    public function testGetTicket(): void
    {
        $client = self::createClient();

        // Сначала создаем заявку
        $data = [
            'title' => 'Тестовая заявка',
            'description' => 'Описание тестовой заявки',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $ticketId = $content['id'];

        // Теперь получаем заявку
        $client->request(
            Request::METHOD_GET,
            '/api/v1/tickets/' . $ticketId,
            [],
            [],
            [
                'HTTP_X-API-KEY' => self::API_KEY,
            ]
        );

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('id', $content);
        self::assertSame($ticketId, $content['id']);
        self::assertArrayHasKey('title', $content);
        self::assertSame('Тестовая заявка', $content['title']);
        self::assertArrayHasKey('description', $content);
        self::assertSame('Описание тестовой заявки', $content['description']);
        self::assertArrayHasKey('author_email', $content);
        self::assertSame('test@example.com', $content['author_email']);
        self::assertArrayHasKey('status', $content);
        self::assertSame('new', $content['status']);
        self::assertArrayHasKey('version', $content);
        self::assertArrayHasKey('created_at', $content);
        self::assertArrayHasKey('updated_at', $content);
        self::assertArrayHasKey('comments', $content);
        self::assertIsArray($content['comments']);
    }

    /**
     * Тест получения несуществующей заявки.
     */
    public function testGetNonExistentTicket(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_GET,
            '/api/v1/tickets/999999',
            [],
            [],
            [
                'HTTP_X-API-KEY' => self::API_KEY,
            ]
        );

        self::assertResponseStatusCodeSame(404);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('code', $content);
        self::assertSame('not_found', $content['code']);
    }

    /**
     * Тест получения списка заявок с фильтрацией по статусу.
     */
    public function testGetTicketsListWithStatusFilter(): void
    {
        $client = self::createClient();

        // Создаем заявку
        $data = [
            'title' => 'Тестовая заявка для фильтрации',
            'description' => 'Описание тестовой заявки для фильтрации',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        // Получаем список заявок с фильтром по статусу
        $client->request(
            Request::METHOD_GET,
            '/api/v1/tickets?status=new',
            [],
            [],
            [
                'HTTP_X-API-KEY' => self::API_KEY,
            ]
        );

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('items', $content);
        self::assertIsArray($content['items']);
        // Должна быть как минимум одна заявка со статусом 'new'
        self::assertGreaterThanOrEqual(1, \count($content['items']));

        // Проверяем, что все заявки в списке имеют статус 'new'
        foreach ($content['items'] as $item) {
            self::assertSame(Status::New->value, $item['status']);
        }
    }

    /**
     * Тест добавления комментария к заявке.
     */
    public function testPostComment(): void
    {
        $client = self::createClient();

        // Сначала создаем заявку
        $data = [
            'title' => 'Тестовая заявка для комментариев',
            'description' => 'Описание тестовой заявки для комментариев',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $ticketId = $content['id'];
        $ticketVersion = 1; // Начальная версия заявки

        // Добавляем комментарий к заявке
        $commentData = [
            'version' => $ticketVersion,
            'comment' => [
                'author' => 'Тестовый автор',
                'message' => 'Тестовое сообщение комментария',
            ],
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets/' . $ticketId . '/events',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($commentData)
        );

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('id', $content);
        self::assertSame($ticketId, $content['id']);
        self::assertArrayHasKey('version', $content);
        self::assertArrayHasKey('status', $content);
    }

    /**
     * Тест добавления комментария к несуществующей заявке.
     */
    public function testPostCommentToNonExistentTicket(): void
    {
        $client = self::createClient();

        $commentData = [
            'version' => 1,
            'comment' => [
                'author' => 'Тестовый автор',
                'message' => 'Тестовое сообщение комментария',
            ],
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets/999999/events',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($commentData)
        );

        self::assertResponseStatusCodeSame(404);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('code', $content);
        self::assertSame('not_found', $content['code']);
    }

    /**
     * Тест добавления комментария с неверной версией заявки.
     */
    public function testPostCommentWithWrongVersion(): void
    {
        $client = self::createClient();

        // Сначала создаем заявку
        $data = [
            'title' => 'Тестовая заявка для проверки версии',
            'description' => 'Описание тестовой заявки для проверки версии',
            'author_email' => 'test@example.com',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $ticketId = $content['id'];

        // Пытаемся добавить комментарий с неверной версией
        $commentData = [
            'version' => 999, // Неверная версия
            'comment' => [
                'author' => 'Тестовый автор',
                'message' => 'Тестовое сообщение комментария',
            ],
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets/' . $ticketId . '/events',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($commentData)
        );

        self::assertResponseStatusCodeSame(409); // HTTP_CONFLICT

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('code', $content);
        self::assertSame('wrong_version', $content['code']);
    }

    /**
     * Тест добавления комментария с изменением статуса заявки.
     */
    public function testPostCommentWithStatusChange(): void
    {
        $client = self::createClient();

        // Сначала создаем заявку
        $data = [
            'title' => 'Тестовая заявка для изменения статуса',
            'description' => 'Описание тестовой заявки для изменения статуса',
            'author_email' => 'user@company.local',
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($data)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $ticketId = $content['id'];
        $ticketVersion = 1; // Начальная версия заявки

        // Добавляем комментарий и меняем статус заявки
        $commentData = [
            'version' => $ticketVersion,
            'comment' => [
                'author' => 'Тестовый автор',
                'message' => 'Тестовое сообщение комментария',
            ],
            'status' => Status::InProgress->value, // Меняем статус на "в работе"
        ];

        $client->request(
            Request::METHOD_POST,
            '/api/v1/tickets/' . $ticketId . '/events',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-KEY' => self::API_KEY,
            ],
            json_encode($commentData)
        );

        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertArrayHasKey('id', $content);
        self::assertSame($ticketId, $content['id']);
        self::assertArrayHasKey('version', $content);
        self::assertArrayHasKey('status', $content);
        self::assertSame('in_progress', $content['status']); // Проверяем, что статус изменился
    }
}
