<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TicketComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of TicketComment
 *
 * @extends ServiceEntityRepository<T>
 */
class TicketCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketComment::class);
    }
}
