<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use Illuminate\Contracts\Support\Arrayable;

final class TokenResource implements Arrayable
{
    /**
     * @param AssignmentModelEnum $mod The model (resource).
     * @param array<string> $ids The ids/uuids.
     */
    public function __construct(
        public readonly AssignmentModelEnum $mod,
        public readonly array $ids,
    ) {
    }

    public function toArray(): array
    {
        return [
            'mod' => $this->mod->value,
            'ids' => $this->ids,
        ];
    }
}
