<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Test;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\TestReason;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function config;

#[Group('case-fragment-test')]
#[Group('fragment')]
final class TestUpToV1Test extends TestCase
{
    use ValidatesModels;

    public function testReasonsCoronamelderShouldValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'schemaVersion' => 1,
            'dateOfTest' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
            'reasons' => [TestReason::coronamelder()->value],
        ]);

        $this->assertEmpty($validationResult);
    }
}
