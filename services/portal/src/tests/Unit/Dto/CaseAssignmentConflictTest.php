<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\CaseAssignmentConflict;
use Illuminate\Contracts\Support\Arrayable;
use Tests\Unit\UnitTestCase;

class CaseAssignmentConflictTest extends UnitTestCase
{
    public function testItCanBeInstantiated(): void
    {
        $caseId = "7654321";
        $assignmentStatus = 'Toegewezen aan gebruiker';
        $caseAssignmentConflict = new CaseAssignmentConflict($caseId, $assignmentStatus);

        $this->assertInstanceOf(CaseAssignmentConflict::class, $caseAssignmentConflict);
        $this->assertEquals($caseId, $caseAssignmentConflict->getcaseId());
        $this->assertEquals($assignmentStatus, $caseAssignmentConflict->getAssignmentStatus());
    }

    public function testItIsArrayable(): void
    {
        $caseId = "7654321";
        $assignmentStatus = 'Verplaatst naar lijst';
        $caseAssignmentConflict = new CaseAssignmentConflict($caseId, $assignmentStatus);

        $this->assertInstanceOf(Arrayable::class, $caseAssignmentConflict);
        $this->assertEquals([
            'caseId' => $caseId,
            'assignmentStatus' => $assignmentStatus,
        ], $caseAssignmentConflict->toArray());
    }
}
