<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Ген даты обновления.
 */
trait UpdatedAtTrait
{
    use DateCompareTrait;
    use DateTimeConverterTrait;

    /**
     * @var \DateTimeImmutable Дата обновления
     */
    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'Дата обновления'])]
    #[Gedmo\Timestampable(on: 'update')]
    protected \DateTimeImmutable $updatedAt;

    /**
     * Дата обновления.
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Задать дату обновления.
     *
     * @param \DateTimeInterface $date Дата обновления
     */
    public function setUpdatedAt(\DateTimeInterface $date): object
    {
        /** @var \DateTimeImmutable */
        $date = $this->convertDateTimeToImmutable($date);

        if (
            !(new \ReflectionProperty(self::class, 'updatedAt'))->isInitialized($this)
            || $this->isDatesDifferent($this->updatedAt, $date)
        ) {
            $this->updatedAt = $date;
        }

        return $this;
    }
}
