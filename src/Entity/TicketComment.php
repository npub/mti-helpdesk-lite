<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Repository\TicketCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Комментарии к заявкам.
 */
#[ORM\Entity(repositoryClass: TicketCommentRepository::class)]
#[ORM\Table('ticket_comments', options: ['comment' => 'Комментарии к заявкам'])]
#[ORM\Index(fields: ['ticket', 'createdAt'], name: 'idx_comments_ticket_created')]
class TicketComment
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true, 'comment' => 'ID комментария'])]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'ticketComments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Ticket $ticket;

    /**
     * Автор.
     */
    #[ORM\Column(length: 100, options: ['comment' => 'Автор'])]
    #[Assert\Length(
        min: 3,
        minMessage: 'Имя автора должно быть не меньше {{ limit }} символов',
        max: 100,
        maxMessage: 'Имя автора должно быть не больше {{ limit }} символов'
    )]
    private string $author;

    /**
     * Сообщение.
     */
    #[ORM\Column(type: Types::TEXT, options: ['comment' => 'Сообщение'])]
    #[Assert\Length(
        min: 3,
        minMessage: 'Сообщение должно быть не меньше {{ limit }} символов',
        max: 4_294_967_295,  // MySQL LONGTEXT типе данных
        maxMessage: 'Сообщение должно быть не больше {{ limit }} символов'
    )]
    private string $message;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
