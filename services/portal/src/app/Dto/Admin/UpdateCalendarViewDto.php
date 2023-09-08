<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use PhpOption\Option;

final class UpdateCalendarViewDto
{
    /**
     * @param Option<string> $label
     * @param Option<array<string>|array{}> $calendarItems
     */
    public function __construct(
        public readonly Option $label,
        public readonly Option $calendarItems,
    )
    {
    }
}
