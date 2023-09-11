<?php

declare(strict_types=1);

namespace App\Services\Policy\PolicyGuideline;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Models\Policy\PolicyGuideline;
use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\IndexPolicyFacts;
use Carbon\CarbonPeriod;

class PolicyGuidelineHandler
{
    public function __construct(private readonly PolicyGuideline $policyGuidelineModel)
    {
    }

    /**
     * @throws PolicyFactMissingException
     */
    public function calculateSourcePeriod(IndexPolicyFacts|ContactPolicyFacts $facts): CarbonPeriod
    {
        $start = $this->policyGuidelineModel->source_start_date_reference->property;
        $end = $this->policyGuidelineModel->source_end_date_reference->property;

        if (!isset($facts->{$start}) || !isset($facts->{$end})) {
            throw new PolicyFactMissingException();
        }

        return new CarbonPeriod(
            $facts->{$start}->addDays(
                $this->policyGuidelineModel->source_start_date_addition,
            ),
            $facts->{$end}->addDays(
                $this->policyGuidelineModel->source_end_date_addition,
            ),
        );
    }

    /**
     * @throws PolicyFactMissingException
     */
    public function calculateContagiousPeriod(IndexPolicyFacts|ContactPolicyFacts $facts): CarbonPeriod
    {
        $start = $this->policyGuidelineModel->contagious_start_date_reference->property;
        $end = $this->policyGuidelineModel->contagious_end_date_reference->property;

        if (!isset($facts->{$start}) || !isset($facts->{$end})) {
            throw new PolicyFactMissingException();
        }

        return new CarbonPeriod(
            $facts->{$start}->addDays(
                $this->policyGuidelineModel->contagious_start_date_addition,
            ),
            $facts->{$end}->addDays(
                $this->policyGuidelineModel->contagious_end_date_addition,
            ),
        );
    }
}
