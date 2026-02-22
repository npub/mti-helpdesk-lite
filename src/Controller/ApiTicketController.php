<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Exception\ApiHttpException;
use App\Controller\Exception\BadRequestException;
use App\Controller\Exception\NotFoundException;
use App\Controller\Exception\UnauthorizedException;
use App\Controller\Exception\UnprocessableEntityException;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Repository\TicketCommentRepository;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * @var string Шаблон формата даты по умолчанию
     */
    public const DATETIME_FORMAT_DEFAULT = 'Y-m-d H:i:s';

    /**
     * @param TicketCommentRepository<TicketComment> $ticketCommentRepository
     * @param TicketRepository<Ticket>               $ticketRepository
     */
    public function __construct(
        /** @var string Ключ авторизации */
        #[Autowire('%env(string:APP_API_KEY)%')]
        protected readonly string $apiKey,
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

        if (0 === \count($data)) {
            throw new UnprocessableEntityException('Ошибка структуры данных запроса', ['Пустой запрос']);
        }

        return $data;
    }

    /**
     * Валидация данных объекта.
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
                $errorsDescriptions[] = $error->getMessage() . ' (поле ' . $error->getPropertyPath() . ')';
            }

            throw new UnprocessableEntityException('Ошибка валидации объекта', $errorsDescriptions);
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
            $post = $this->parseRequestData($request);

            $ticket = new Ticket();
            $ticket->setTitle($post['title'] ?? '');
            $ticket->setDescription($post['description'] ?? '');
            $ticket->setAuthorEmail($post['author_email'] ?? '');
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
                'created_at' => $ticket->getCreatedAt()->format(self::DATETIME_FORMAT_DEFAULT),
                'updated_at' => $ticket->getUpdatedAt()->format(self::DATETIME_FORMAT_DEFAULT),
                'comments' => [],
            ];

            $comments = $this->ticketCommentRepository->findBy(['ticket' => $ticket]);
            foreach ($comments as $comment) { // но лучше $ticket->getComments()
                $output['comments'][] = [
                    'id' => $comment->getId(),
                    'author' => $comment->getAuthor(),
                    'message' => $comment->getMessage(),
                    'created_at' => $comment->getCreatedAt()->format(self::DATETIME_FORMAT_DEFAULT),
                ];
            }

            return $this->json($output);
        } catch (ApiHttpException $e) {
            return $e->createJsonResponse();
        } catch (\Throwable $th) {
            return ApiHttpException::createUnknownErrorJsonResponse($th);
        }
    }

    /**
     * Количество записей на странице.
     */
    public const RECORDS_PER_PAGE = 10;

    /**
     * Получение списка заявок (фильтры + пагинация).
     *
     * Фильтры (опциональны):
     *   status — фильтр по статусу
     *   q — поиск по title и description
     *   page / per_page — пагинация (по умолчанию page = 1, per_page = 10)
     *   sort — created_at или updated_at (направление через «-» (символ минуса) перед значением)
     *
     * GET: /api/v1/tickets?status=new&q=отчёт&page=1&per_page=20&sort=-created_at
     */
    #[Route('tickets', name: 'app_api_ticket_list', methods: Request::METHOD_GET)]
    public function ticketsList(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $recordsPerPage = (int) $request->query->get('per_page', self::RECORDS_PER_PAGE);
            if ($page <= 0 || 0 >= $recordsPerPage || 100 < $recordsPerPage) {
                throw new BadRequestException('Заданы неверные параметры пагинации (должно быть page > 1, per_page: от 1 до 100)');
            }

            $qb = $this->ticketRepository->createQueryBuilder('t')
                // ->addOrderBy('l.id', Order::Descending->value)
                ->setFirstResult($recordsPerPage * ($page - 1))
                ->setMaxResults($recordsPerPage)
            ;

            // Фильтр по статусу
            $status = $this->loadStatusFromValue($request->query->get('status'));
            if (null !== $status) {
                $qb
                    ->andWhere('t.status = :status_filter')
                    ->setParameter('status_filter', $status)
                ;
            }

            // Фильтр по названию или описанию
            $q = $request->query->get('q');
            if (null !== $q) {
                $qb
                    ->andWhere('t.title LIKE :q_filter OR t.description LIKE :q_filter')
                    ->setParameter('q_filter', '%' . $q . '%')
                ;
            }

            // Сортировка
            $sort = $request->query->get('sort');
            if (null !== $sort) {
                $direction = str_starts_with($sort, '-') ? Order::Descending : Order::Ascending;
                $field = match (str_replace('-', '', $sort)) {
                    'created_at' => 'createdAt',
                    'updated_at' => 'updatedAt',
                    default => null,
                };
                if (null === $field) {
                    throw new BadRequestException('Указан неверный параметр сортировки (допустимые значения: [-]created_at, [-]updated_at)');
                }

                $qb
                    ->addOrderBy('t.' . $field, $direction->value)
                ;
            }

            $ticketsPaginator = new Paginator($qb, true);

            $tickets = [];
            foreach ($ticketsPaginator as $ticket) {
                /** @var Ticket $ticket */
                $tickets[] = [
                    'id' => $ticket->getId(),
                    'title' => $ticket->getTitle(),
                    'status' => $ticket->getStatus()->value,
                    'created_at' => $ticket->getCreatedAt()->format(self::DATETIME_FORMAT_DEFAULT),
                    'updated_at' => $ticket->getUpdatedAt()->format(self::DATETIME_FORMAT_DEFAULT),
                ];
            }

            return $this->json([
                'page' => $page,
                'per_page' => $recordsPerPage,
                'total' => \count($ticketsPaginator),
                'items' => $tickets,
            ]);
        } catch (ApiHttpException $e) {
            return $e->createJsonResponse();
        } catch (\Throwable $th) {
            return ApiHttpException::createUnknownErrorJsonResponse($th);
        }
    }

    /**
     * Проверка и конвертация в Enum значение статуса.
     *
     * @throws BadRequestException
     */
    private function loadStatusFromValue(?string $status): ?Status
    {
        if (null !== $status) {
            $status = Status::tryFrom($status);
            if (null === $status) {
                throw new BadRequestException('Указан неверный статус (допустимые значения: ' . implode(', ', Status::values()) . ')');
            }
        }

        return $status;
    }

    /**
     * Добавление комментария и смена статуса (опционально, если не передать, то сохраняется прежний).
     *
     * POST: /api/v1/tickets/{id}/events
     */
    #[Route('tickets/{id<\d+>}/events', name: 'app_api_ticket_comment_post', methods: Request::METHOD_POST)]
    public function postComment(int $id, Request $request): JsonResponse
    {
        try {
            /**
             * @var array{version: int|null, comment: array{author: string|null, message: string|null}|null, status: string|null}
             */
            $post = $this->parseRequestData($request);
            $ticketVersion = $post['version'] ?? null;
            if (null === $ticketVersion) {
                throw new BadRequestException('Не указана версия заявки');
            }

            $comment = new TicketComment();
            $comment->setAuthor($post['comment']['author'] ?? '');
            $comment->setMessage($post['comment']['message'] ?? '');
            $this->validateObject($comment);

            $this->em->beginTransaction();
            // Начало транзакции выше представлено в ознакомительных целях,
            // т.к. тут простой код и всего один em->flush() — соответственно «под капотом» Doctrine выполняет все запросы внутри транзакции автоматически.
            // Но если бы код был сложнее, с вложенностью и т.п., то оборачивание всего в большую транзакцию было бы востребовано.

            /**
             * @var Ticket|null
             */
            $ticket = $this->ticketRepository->find($id, LockMode::OPTIMISTIC, $ticketVersion);
            if (null === $ticket) {
                throw new NotFoundException('Заявка не найдена');
            }

            $this->em->persist($comment);
            $ticket->addComment($comment);

            $status = $this->loadStatusFromValue($post['status'] ?? null);
            if (null !== $status) {
                $ticket->setStatus($status);
            }
            $ticket->setUpdatedNow();

            $this->em->flush();
            $this->em->commit();

            return $this->json([
                'id' => $ticket->getId(),
                'version' => $ticket->getVersion(),
                'status' => $ticket->getStatus()->value,
            ]);
        } catch (ApiHttpException $e) {
            return $e->createJsonResponse();
        } catch (OptimisticLockException $e) {
            return (new ApiHttpException('Ошибка блокировки заявки на изменение (передана неактуальна версия)', 'wrong_version', codeHttp: Response::HTTP_CONFLICT))->createJsonResponse();
        } catch (\Throwable $th) {
            return ApiHttpException::createUnknownErrorJsonResponse($th);
        }
    }
}
