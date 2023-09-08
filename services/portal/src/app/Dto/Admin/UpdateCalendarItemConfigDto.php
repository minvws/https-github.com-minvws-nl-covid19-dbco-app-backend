<?php

declare(strict_types=1);

namespace App\Dto\Admin;

final class UpdateCalendarItemConfigDto
{
    public function __construct(
        public readonly bool $isHidden,
    ) {
    }

    public function toEloquentAttributes(): array
    {
        return [
            'is_hidden' => $this->isHidden,
        ];
    }
}
