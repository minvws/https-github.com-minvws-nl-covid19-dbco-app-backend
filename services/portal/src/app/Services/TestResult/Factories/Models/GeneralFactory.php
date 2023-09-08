<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\Test;
use App\Models\TestResult\General;
use App\Models\Versions\TestResult\General\GeneralV1;

final class GeneralFactory
{
    public static function create(Test $test): GeneralV1
    {
        /** @var GeneralV1 $general */
        $general = General::getSchema()->getVersion(1)->newInstance();

        $general->testLocation = $test->testLocation;
        $general->testLocationCategory = $test->testLocationCategory;

        return $general;
    }
}
