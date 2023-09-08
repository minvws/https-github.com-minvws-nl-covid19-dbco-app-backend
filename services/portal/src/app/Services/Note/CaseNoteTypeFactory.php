<?php

declare(strict_types=1);

namespace App\Services\Note;

use MinVWS\DBCO\Enum\Models\CaseNoteType;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;

class CaseNoteTypeFactory
{
    public static function fromCasequalityFeedback(CasequalityFeedback $feedback): CaseNoteType
    {
        switch ($feedback) {
            case CasequalityFeedback::approveAndArchive():
                return CaseNoteType::caseCheckedApprovedClosed();
            case CasequalityFeedback::rejectAndReopen():
                return CaseNoteType::caseCheckedRejectedReturned();
            case CasequalityFeedback::archive():
                return CaseNoteType::caseNotCheckedClosed();
            case CasequalityFeedback::complete():
                return CaseNoteType::caseNotCheckedReturned();
        }

        return CaseNoteType::caseNote();
    }
}
