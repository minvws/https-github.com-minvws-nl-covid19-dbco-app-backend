<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Note;

use App\Services\Note\CaseNoteTypeFactory;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class CaseNoteTypeFactoryTest extends UnitTestCase
{
    #[DataProvider('caseNoteTypeFactoryProvider')]
    public function testCaseNoteTypeFactory(CaseNoteType $expectedType, CasequalityFeedback $casequalityFeedback): void
    {
        $this->assertSame($expectedType, CaseNoteTypeFactory::fromCasequalityFeedback($casequalityFeedback));
    }

    public static function caseNoteTypeFactoryProvider(): array
    {
        return [
            [CaseNoteType::caseCheckedApprovedClosed(), CasequalityFeedback::approveAndArchive()],
            [CaseNoteType::caseCheckedRejectedReturned(), CasequalityFeedback::rejectAndReopen()],
            [CaseNoteType::caseNotCheckedClosed(), CasequalityFeedback::archive()],
            [CaseNoteType::caseNotCheckedReturned(), CasequalityFeedback::complete()],
        ];
    }
}
