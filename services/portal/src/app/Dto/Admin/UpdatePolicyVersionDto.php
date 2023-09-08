<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PhpOption\Option;

class UpdatePolicyVersionDto implements Arrayable
{
    /**
     * @param Option<string> $name
     * @param Option<PolicyVersionStatus> $status
     * @param Option<CarbonImmutable> $startDate
     */
    public function __construct(
        public Option $name,
        public Option $status,
        public Option $startDate,
    )
    {
    }

    public function toArray(): array
    {
        return Collection::make([
            'name' => $this->name,
            'status' => $this->status,
            'start_date' => $this->startDate,
        ])
            ->filter(static fn (Option $value): bool =>$value->isDefined())
            ->map(static fn (Option $value): mixed => $value->get())
            ->toArray();
    }
}
