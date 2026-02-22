<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\UpdatedAtTrait;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Заявка.
 */
#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table('tickets', options: ['comment' => 'Заявки'])]
#[ORM\Index(fields: ['status', 'createdAt'], name: 'idx_tickets_status_created')]
#[ORM\Index(fields: ['updatedAt'], name: 'idx_tickets_updated')]
#[ORM\HasLifecycleCallbacks]
class Ticket
{
    use CreatedAtTrait, UpdatedAtTrait { CreatedAtTrait::isDatesDifferent insteadof UpdatedAtTrait; }

    /**
     * ID заявки.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true, 'comment' => 'ID заявки'])]
    private int $id;

    /**
     * Версия заявки.
     */
    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true, 'default' => 1, 'comment' => 'Версия'])]
    #[ORM\Version]
    private int $version = 1;

    /**
     * Название.
     */
    #[ORM\Column(length: 200, options: ['comment' => 'Название'])]
    #[Assert\NotBlank(message: 'Название не может быть пустым')]
    #[Assert\Length(
        min: 3,
        minMessage: 'Название должно быть не меньше {{ limit }} символов',
        max: 200,
        maxMessage: 'Название должно быть не больше {{ limit }} символов'
    )]
    private string $title;

    /**
     * Описание.
     */
    #[ORM\Column(type: Types::TEXT, options: ['comment' => 'Описание'])]
    private string $description;

    /**
     * Email автора.
     */
    #[ORM\Column(length: 255, options: ['comment' => 'Email автора'])]
    #[Assert\NotBlank(message: 'Email автора не может быть пустым')]
    #[Assert\Email(message: 'Неверный формат email')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email должен быть не больше {{ limit }} символов'
    )]
    private string $authorEmail;

    /**
     * Статус.
     */
    #[ORM\Column(type: Types::ENUM, enumType: Status::class, options: ['default' => Status::New, 'comment' => 'Статус'])]
    private Status $status = Status::New;

    /**
     * @var Collection<int, TicketComment>|ArrayCollection<int, TicketComment> Комментарии к заявке
     */
    #[ORM\OneToMany(targetEntity: TicketComment::class, mappedBy: 'ticket')]
    private Collection $ticketComments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->ticketComments = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): static
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Collection<int, TicketComment>
     */
    public function getTicketComments(): Collection
    {
        return $this->ticketComments;
    }

    public function addTicketComment(TicketComment $ticketComment): static
    {
        if (!$this->ticketComments->contains($ticketComment)) {
            $this->ticketComments->add($ticketComment);
            $ticketComment->setTicket($this);
        }

        return $this;
    }

    public function removeTicketComment(TicketComment $ticketComment): static
    {
        $this->ticketComments->removeElement($ticketComment);

        return $this;
    }
}
