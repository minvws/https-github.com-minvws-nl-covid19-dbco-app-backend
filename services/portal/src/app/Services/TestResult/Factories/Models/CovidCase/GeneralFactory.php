<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\CovidCase\General;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Versions\CovidCase\General\GeneralV2;
use App\Services\TestResult\Factories\Enums\TestResultSourceFactory;

use function now;

final class GeneralFactory
{
    public static function create(TestResultReport $testResultReport, EloquentOrganisation $organisation): GeneralV2
    {
        /** @var GeneralV2 $general */
        $general = General::getSchema()->getVersion(2)->newInstance();

        $general->organisation = $organisation->toOrganisation();
        $general->source = TestResultSourceFactory::fromSource($testResultReport->test->source)->value;
        $general->createdAt = now();

        return $general;
    }
}
