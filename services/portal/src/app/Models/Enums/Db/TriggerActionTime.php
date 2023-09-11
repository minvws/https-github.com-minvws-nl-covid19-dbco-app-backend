<?php

declare(strict_types=1);

namespace App\Models\Enums\Db;

enum TriggerActionTime
{
    case BEFORE_INSERT;
    case AFTER_INSERT;
    case BEFORE_UPDATE;
    case AFTER_UPDATE;

    public function statement(): string
    {
        return match ($this) {
            self::BEFORE_INSERT => 'BEFORE INSERT',
            self::AFTER_INSERT => 'AFTER INSERT',
            self::BEFORE_UPDATE => 'BEFORE UPDATE',
            self::AFTER_UPDATE => 'AFTER UPDATE',
        };
    }

    public function alias(): string
    {
        return match ($this) {
            self::BEFORE_INSERT => 'bi',
            self::AFTER_INSERT => 'ai',
            self::BEFORE_UPDATE => 'bu',
            self::AFTER_UPDATE => 'au',
        };
    }
}
