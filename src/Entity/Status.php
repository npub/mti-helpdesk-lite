<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\EnumToArrayTrait;

/**
 * Статус заявки.
 */
enum Status: string
{
    use EnumToArrayTrait;

    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Closed = 'closed';

    /**
     * Имя статуса.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'новая',
            self::InProgress => 'в работе',
            self::Done => 'обработана',
            self::Closed => 'закрыта',
        };
    }
}
