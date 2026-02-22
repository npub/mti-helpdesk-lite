<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Exception\ApiHttpException;
use App\Controller\Exception\NotFoundException;
use App\Controller\Exception\UnauthorizedException;
use App\Controller\Exception\UnprocessableEntityException;
use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Repository\TicketCommentRepository;
use App\Repository\TicketRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/')]
final class ApiTicketController extends AbstractController
{
    /** @var string Название заголовка с ключом авторизации */
    public const API_KEY_NAME = 'X-API-KEY';

    /**
     * @param TicketCommentRepository<TicketComment> $ticketCommentRepository
     * @param TicketRepository<Ticket> $ticketRepository
     */
    public function __construct(
        /** @var string Ключ авторизации */
        #[Autowire('%env(string:APP_API_KEY)%')]
        protected readonly string $apiKey,
        protected readonly Connection $conn,
        protected readonly EntityManagerInterface $em,
        protected readonly TicketCommentRepository $ticketCommentRepository,
        protected readonly TicketRepository $ticketRepository,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Проверка авторизации запроса.
     *
     * @throws UnauthorizedException
     */
    private function validateRequest(Request $request): void
    {
        if ($request->headers->get(self::API_KEY_NAME) !== $this->apiKey) {
            throw new UnauthorizedException('Неверный ключ API (проверьте заголовок ' . self::API_KEY_NAME . ')');
        }
    }

    /**
     * Получение данных из запроса.
     *
     * @return array<string, mixed>
     */
    private function parseRequestData(Request $request): array
    {
        $this->validateRequest($request);

        /** @var array<string, mixed> */
        $data = $request->toArray();

        if (\count($data) === 0) {
            throw new UnprocessableEntityException('Ошибка структуры данных запроса', ['Пустой запрос']);
        }

        return $data;
    }

    /**
     * Проверка данных объекта перед сохранением в базу данных.
     * (параметры валидации настраиваются в атрибутах Entity).
     *
     * @throws UnauthorizedException
     */
    private function validateObject(Ticket|TicketComment $object): void
    {
        $errors = $this->validator->validate($object);
        if (\count($errors) > 0) {
            $errorsDescriptions = [];
            foreach ($errors as $error) {
                $errorsDescriptions[] = $error->getMessage();
            }

            throw new UnprocessableEntityException('Ошибка валидации данных объекта', $errorsDescriptions);
        }
    }

    /**
     * Создание заявки.
     *
     * POST: /api/v1/tickets
     */
    #[Route('tickets', name: 'app_api_ticket_create', methods: Request::METHOD_POST)]
    public function createTicket(Request $request): JsonResponse
    {
        try {
            /**
             * @var array{title: string|null, description: string|null, author_email: string|null}
             */
            $requestData = $this->parseRequestData($request);

            $ticket = new Ticket();
            $ticket->setTitle($requestData['title'] ?? '');
            $ticket->setDescription($requestData['description'] ?? '');
            $ticket->setAuthorEmail($requestData['author_email'] ?? '');
            $this->validateObject($ticket);

            $this->em->persist($ticket);
            $this->em->flush();

            return $this->json([
                'id' => $ticket->getId(),
            ], Response::HTTP_CREATED);
        } catch (ApiHttpException $e) {
            return $e->createJsonResponse();
        } catch (\Throwable $th) {
            return ApiHttpException::createUnknownErrorJsonResponse($th);
        }
    }

    /**
     * Получение карточки заявки с комментариями.
     *
     * GET: /api/v1/tickets/{id}
     */
    #[Route('tickets/{id<\d+>}', name: 'app_api_ticket_view', methods: Request::METHOD_GET)]
    public function ticket(int $id): JsonResponse
    {
        try {
            /**
             * @var Ticket|null
             */
            $ticket = $this->ticketRepository->find($id);
            if (null === $ticket) {
                throw new NotFoundException('Заявка не найдена');
            }

            $output = [
                'id' => $ticket->getId(),
                'title' => $ticket->getTitle(),
                'description' => $ticket->getDescription(),
                'author_email' => $ticket->getAuthorEmail(),
                'status' => $ticket->getStatus()->value,
                'version' => $ticket->getVersion(),
                'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $ticket->getUpdatedAt()->format('Y-m-d H:i:s'),
                'comments' => [],
            ];

            $comments = $this->ticketCommentRepository->findBy(['ticket' => $ticket]);
            foreach ($comments as $comment) { // но лучше $ticket->getComments()
                $output['comments'][] = [
                    'id' => $comment->getId(),
                    'author' => $comment->getAuthor(),
                    'message' => $comment->getMessage(),
                    'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }

            return $this->json($output);

        } catch (ApiHttpException $e) {
            return $e->createJsonResponse();
        } catch (\Throwable $th) {
            return ApiHttpException::createUnknownErrorJsonResponse($th);
        }
    }
}
