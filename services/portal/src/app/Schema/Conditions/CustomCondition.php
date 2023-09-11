<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;
use Closure;

class CustomCondition extends Condition
{
    /**
     * @param array<string> $fields
     */
    public function __construct(
        private readonly array $fields,
        private readonly Closure $condition,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $data): bool
    {
        $condition = $this->condition;
        return $condition($data);
    }

    protected function buildString(int $level): string
    {
        return Documentation::get('operator', 'custom') ?? '?';
    }
}
