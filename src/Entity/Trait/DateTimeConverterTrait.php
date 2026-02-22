<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use DateTimeImmutable;

/**
 * Ген конвертации дат.
 */
trait DateTimeConverterTrait
{
    /**
     * Конвертация даты в DateTimeImmutable.
     */
    public function convertDateTimeToImmutable(?\DateTimeInterface $date): ?\DateTimeImmutable
    {
        if (null === $date || $date instanceof \DateTimeImmutable) {
            return $date;
        }

        return \DateTimeImmutable::createFromInterface($date);
    }
}
