<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Listeners\Osiris\LogOsirisValidationResult;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Tests\Faker\WithFaker;
use Tests\Unit\UnitTestCase;

#[Group('osiris')]
class LogOsirisValidationResultTest extends UnitTestCase
{
    use WithFaker;

    private LoggerInterface $logger;
    private LogOsirisValidationResult $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->listener = new LogOsirisValidationResult($this->logger);
    }

    public function testWhenCaseValidationRaisesWarningWritesLog(): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());

        $this->logger->expects('info')
            ->withArgs(function (string $arg1, array $arg2) use ($case, $caseExportType): bool {
                $this->assertEquals('Osiris validation warning(s) found', $arg1);
                $this->assertEquals(
                    [
                        'caseUuid' => $case->uuid,
                        'caseExportType' => $caseExportType,
                        'validationResult' => '{"foo":"bar"}',
                    ],
                    $arg2,
                );

                return true;
            });

        $this->listener->whenCaseValidationRaisesWarning(
            new CaseValidationRaisesWarning($case, ['foo' => 'bar'], $caseExportType),
        );
    }

    public function testWhenCaseValidationRaisesNoticeWritesLog(): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());

        $this->logger->expects('info')
            ->withArgs(function (string $arg1, array $arg2) use ($case, $caseExportType): bool {
                $this->assertEquals('Osiris validation notice(s) found', $arg1);
                $this->assertEquals(
                    [
                        'caseUuid' => $case->uuid,
                        'caseExportType' => $caseExportType,
                        'validationResult' => '{"foo":"bar"}',
                    ],
                    $arg2,
                );

                return true;
            });

        $this->listener->whenCaseValidationRaisesNotice(
            new CaseValidationRaisesNotice($case, ['foo' => 'bar'], $caseExportType),
        );
    }
}
