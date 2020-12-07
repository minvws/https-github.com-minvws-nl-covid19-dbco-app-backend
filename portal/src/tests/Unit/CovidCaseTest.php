<?php

namespace Tests\Unit;

use App\Models\CovidCase;
use PHPUnit\Framework\TestCase;

class CovidCaseTest extends TestCase
{
    /**
     * This test uses a data provider with reflection to make sure we trigger
     * a defect as soon as a new status is added without a matching label
     *
     * @testdox CovidCase provides a label for status $status
     * @dataProvider caseStatusConstantsProvider
     */
    public function testEveryCaseStatusHasALabel(string $status): void
    {
        $this->assertNotEmpty(CovidCase::statusLabel($status));
    }

    public function testInvalidCaseStatusIsHandledGracefully(): void
    {
        $this->assertEquals("Onbekend", CovidCase::statusLabel(null));
        $this->assertEquals("Onbekend", CovidCase::statusLabel(false));
        $this->assertEquals("Onbekend", CovidCase::statusLabel(''));
    }

    /**
     * @testdox Case with status=$status has editable=$editable
     * @dataProvider caseStatusEditableProvider
     */
    public function testCaseStatusDeterminesIfCaseCanBeEdited(string $status, bool $editable): void
    {
        $case = $this->createPartialMock(CovidCase::class, ['caseStatus']);
        $case->method('caseStatus')
            ->willReturn($status);

        $this->assertSame($editable, $case->isEditable());
    }

    public static function caseStatusConstantsProvider(): array
    {
        $caseModel = new \ReflectionClass(CovidCase::class);
        $possibleStatuses = [];

        foreach ($caseModel->getConstants() as $constantName => $constantValue) {
            if (strpos($constantName, 'STATUS_') === 0) {
                $possibleStatuses[] = [$constantValue];
            }
        }

        return $possibleStatuses;
    }

    public static function caseStatusEditableProvider(): array
    {
        $caseModel = new \ReflectionClass(CovidCase::class);
        $possibleStatuses = [];

        // Default: a CovidCase is editable
        foreach ($caseModel->getConstants() as $constantName => $constantValue) {
            $possibleStatuses[$constantValue] = [$constantValue, true];
        }

        // A short list of statuses prevents editing
        $possibleStatuses[CovidCase::STATUS_COMPLETED] = [CovidCase::STATUS_COMPLETED, false];
        $possibleStatuses[CovidCase::STATUS_ARCHIVED] = [CovidCase::STATUS_ARCHIVED, false];
        $possibleStatuses[CovidCase::STATUS_EXPIRED] = [CovidCase::STATUS_EXPIRED, false];

        return $possibleStatuses;
    }
}
