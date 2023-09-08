<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Pregnancy;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function config;

#[Group('fragment')]
final class PregnancyTest extends TestCase
{
    use ValidatesModels;

    public function testWithDueDateFarBeforeCaseCreationDateShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Pregnancy::class, [
            'schemaVersion' => 1,
            'dueDate' => CarbonImmutable::now()->subDays(config('misc.validations.maxDueDateBeforeCaseCreationInDays') + 1)
                ->format('Y-m-d'),
            'maxDueDateBeforeCaseCreation' => CarbonImmutable::now()->copy()->sub(
                config('misc.validations.maxDueDateBeforeCaseCreationInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);

        $this->assertArrayHasKey('AfterOrEqual', $validationResult['warning']['failed']['dueDate']);
    }

    public function testWithDueDateFarAfterCaseCreationDateShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Pregnancy::class, [
            'schemaVersion' => 1,
            'dueDate' => CarbonImmutable::now()->addMonths(config('misc.validations.maxDueDateAfterCaseCreationInMonths') + 1)
                ->format('Y-m-d'),
            'maxDueDateAfterCaseCreation' => CarbonImmutable::now()->copy()->add(
                config('misc.validations.maxDueDateAfterCaseCreationInMonths') . ' months',
            )->format(
                'Y-m-d',
            ),
        ]);

        $this->assertArrayHasKey('BeforeOrEqual', $validationResult['warning']['failed']['dueDate']);
    }
}
