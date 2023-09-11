<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Policy\PolicyVersion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyVersionCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PolicyVersion $policyVersion)
    {
    }
}
