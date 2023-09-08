<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Events\Osiris\CaseExportSucceeded;
use App\Listeners\Osiris\ProcessOsirisNumber;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\ValueObjects\OsirisNumber;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Tests\Faker\WithFaker;
use Tests\Unit\UnitTestCase;

use function array_keys;

#[Group('osiris')]
#[Group('osiris-case-export')]
class ProcessOsirisNumberTest extends UnitTestCase
{
    use WithFaker;

    private LoggerInterface&Mockery\MockInterface $logger;
    private ProcessOsirisNumber $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->listener = new ProcessOsirisNumber($this->logger);
    }

    public function testIsIdleIfOsirisNumberAlreadyStoredAndMatches(): void
    {
        $osirisNumber = $this->faker->randomNumber(6);
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->osirisNumber = $osirisNumber;
        $event = new CaseExportSucceeded(
            $case,
            new CaseExportResult(
                new OsirisNumber($osirisNumber),
                $this->faker->numberBetween(9, 10),
                $this->faker->bothify('#?#-?#?-#?#'),
                $this->faker->uuid(),
            ),
            $this->faker->randomElement(CaseExportType::cases()),
        );

        $case->expects('save')
            ->never();

        $this->logger->expects('warning')
            ->never();

        ($this->listener)($event);
    }

    public function testLogWarningIfOsirisNumberAlreadyStoredButNotMatching(): void
    {
        $osirisNumberInResponse = $this->faker->randomNumber(6);
        $osirisNumberInDatabase = $osirisNumberInResponse + 1;
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->osirisNumber = $osirisNumberInDatabase;
        $event = new CaseExportSucceeded(
            $case,
            new CaseExportResult(
                new OsirisNumber($osirisNumberInResponse),
                $this->faker->numberBetween(9, 10),
                $this->faker->bothify('#?#-?#?-#?#'),
                $this->faker->uuid(),
            ),
            $this->faker->randomElement(CaseExportType::cases()),
        );

        $case->expects('save')
            ->never();

        $this->logger->expects('warning')
            ->withArgs(function (string $arg1, array $arg2) use ($osirisNumberInResponse, $osirisNumberInDatabase): bool {
                $this->assertEquals('Mismatch between Osiris number received in response and stored in database', $arg1);
                $this->assertEquals(
                    [
                        'osirisNumberInResponse',
                        'osirisNumberInDatabase',
                        'caseUuid',
                        'caseExportType',
                    ],
                    array_keys($arg2),
                );
                $this->assertEquals($osirisNumberInResponse, $arg2['osirisNumberInResponse']);
                $this->assertEquals($osirisNumberInDatabase, $arg2['osirisNumberInDatabase']);

                return true;
            });

        ($this->listener)($event);
    }

    public function testStoresOsirisNumberIfCasePropertyIsEmpty(): void
    {
        $osirisNumber = $this->faker->randomNumber(6);
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->osirisNumber = null;
        $event = new CaseExportSucceeded(
            $case,
            new CaseExportResult(
                new OsirisNumber($osirisNumber),
                $this->faker->numberBetween(9, 10),
                $this->faker->bothify('#?#-?#?-#?#'),
                $this->faker->uuid(),
            ),
            $this->faker->randomElement(CaseExportType::cases()),
        );

        $case->expects('save');

        $this->logger->expects('warning')
            ->never();

        ($this->listener)($event);

        $this->assertEquals($osirisNumber, $case->osirisNumber);
    }
}
