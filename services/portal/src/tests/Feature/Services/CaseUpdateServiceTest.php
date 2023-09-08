<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\CaseUpdate\CaseUpdateService;
use Tests\Feature\FeatureTestCase;

use function collect;

class CaseUpdateServiceTest extends FeatureTestCase
{
    public function testConvertIntakeToCaseUpdateCaseLabels(): void
    {
        $organisation = $this->createOrganisation();
        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation);
        $caseLabel3 = $this->createCaseLabelForOrganisation($organisation);

        $intake = $this->createIntakeForOrganisationWithLabels($organisation, [], collect([
            $caseLabel1,
            $caseLabel2,
        ]));

        $case = $this->createCaseForOrganisation($organisation);
        $case->caseLabels()->sync($caseLabel3);
        $case->save();
        $this->assertCount(1, $case->caseLabels);

        $caseUpdateService = $this->app->get(CaseUpdateService::class);
        $caseUpdateService->convertIntakeToCaseUpdate($intake, $case);

        $case->refresh();
        $this->assertCount(3, $case->caseLabels);
        $this->assertTrue($case->caseLabels->contains($caseLabel1));
        $this->assertTrue($case->caseLabels->contains($caseLabel2));
        $this->assertTrue($case->caseLabels->contains($caseLabel3)); // assert it's not deleted
    }
}
