<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Ген даты создания.
 */
trait CreatedAtTrait
{
    use DateCompareTrait;
    use DateTimeConverterTrait;

    /**
     * @var \DateTimeImmutable Дата создания
     */
    #[ORM\Column(updatable: false, options: ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата создания'])]
    #[Gedmo\Timestampable(on: 'create')]
    protected \DateTimeImmutable $createdAt;

    /**
     * Дата создания.
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Задать дату создания.
     */
    public function setCreatedAt(\DateTimeInterface $date): object
    {
        /** @var \DateTimeImmutable */
        $date = $this->convertDateTimeToImmutable($date);

        if (
            !(new \ReflectionProperty(self::class, 'createdAt'))->isInitialized($this)
            || $this->isDatesDifferent($this->createdAt, $date)
        ) {
            $this->createdAt = $date;
        }

        return $this;
    }
}
