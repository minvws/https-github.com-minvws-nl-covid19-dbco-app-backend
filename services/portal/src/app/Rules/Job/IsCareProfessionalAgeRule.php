<?php

declare(strict_types=1);

namespace App\Rules\Job;

use App\Helpers\JobSectorHelper;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;
use MinVWS\DBCO\Enum\Models\JobSector;

use function is_array;
use function is_null;
use function is_string;
use function trans;

/**
 * Validates if the dateOfBirth ($value) of Care Professional is before (younger) or
 * after (older) than the given age ($this->ageInYears).
 */
class IsCareProfessionalAgeRule implements ImplicitRule
{
    public function __construct(
        private readonly ?CarbonImmutable $dateOfBirth,
        private readonly int $ageInYears,
        private readonly string $dateCompare,
        private readonly string $message,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value) || is_null($this->dateOfBirth)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        $sectors = JobSector::fromArray($value);

        if (!$this->isCareProfessional($sectors)) {
            return true;
        }

        $dateCompare = $this->dateCompare;
        return $this->dateOfBirth->addYears($this->ageInYears)->{$dateCompare}(CarbonImmutable::now());
    }

    public function message(): string
    {
        $result = trans('validation.' . $this->message);
        return is_string($result) ? $result : $this->message;
    }

    /**
     * @param array $sectors<JobSector>
     */
    private function isCareProfessional(array $sectors): bool
    {
        return JobSectorHelper::containsCareGroup($sectors);
    }
}
