<?php

declare(strict_types=1);

namespace App\Rules\Policy;

use App\Models\Policy\PolicyVersion;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use Webmozart\Assert\Assert;

class UniqueStartDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Assert::string($value);
        $existingPolicyVersions = PolicyVersion::whereDate('start_date', '=', CarbonImmutable::parse($value))
            ->where('status', '=', PolicyVersionStatus::activeSoon())
            ->count();

        if ($existingPolicyVersions <= 0) {
            return;
        }

        $fail('validation.uniquestartdate')->translate();
    }
}
