<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Policy\PolicyGuideline;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyGuidelineCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PolicyGuideline $policyGuideline)
    {
    }
}
