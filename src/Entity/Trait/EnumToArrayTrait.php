<?php

declare(strict_types=1);

namespace App\Entity\Trait;

/**
 * Ген получения массивов из перечислений.
 */
trait EnumToArrayTrait
{
    /**
     * Имена.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Значения.
     *
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Массив значение-имя.
     *
     * @return array<string|int, string>
     */
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }
}
