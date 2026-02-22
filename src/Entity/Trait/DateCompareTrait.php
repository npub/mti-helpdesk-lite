<?php

declare(strict_types=1);

namespace App\Entity\Trait;

/**
 * Ген сравнения дат.
 */
trait DateCompareTrait
{
    /**
     * Проверить 2 даты (любой реализации \DateTimeInterface) на тождественность.
     * (это предотвращает мнимые обновления поля в Doctrine).
     */
    public function isDatesDifferent(?\DateTimeInterface $dateA, ?\DateTimeInterface $dateB): bool
    {
        return (null === $dateA xor null === $dateB)
            || (null !== $dateA && null !== $dateB && $dateA->getTimestamp() !== $dateB->getTimestamp());
    }
}
