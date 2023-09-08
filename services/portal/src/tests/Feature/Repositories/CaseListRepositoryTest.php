<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\CaseList\ListOptions;
use App\Repositories\CaseListRepository;
use Tests\Feature\FeatureTestCase;

class CaseListRepositoryTest extends FeatureTestCase
{
    private CaseListRepository $caseListRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseListRepository = $this->app->get(CaseListRepository::class);
    }

    public function testListCaseListWithStats(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));
        $this->createCaseListForOrganisation($organisation);

        // Build list options
        $listOptions = new ListOptions();
        $listOptions->types = [ListOptions::TYPE_LIST];
        $listOptions->stats = true;

        // Get the list from the repository and assert it's paginator
        $response = $this->caseListRepository->listCaseLists($listOptions);
        $this->assertEquals(1, $response->total());

        // Make sure stats are within the response
        $responseCaseList = $response->items()[0];
        $this->assertArrayHasKey('assigned_cases_count', $responseCaseList);
        $this->assertArrayHasKey('unassigned_cases_count', $responseCaseList);
        $this->assertArrayHasKey('completed_cases_count', $responseCaseList);
        $this->assertArrayHasKey('archived_cases_count', $responseCaseList);
    }

    public function testGetCaseListByUuidNonExisting(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        $caseList = $this->caseListRepository->getCaseListByUuid('non-existing', false);
        $this->assertNull($caseList);
    }

    public function testGetCaseListByUuidWithoutStats(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));
        $caseList = $this->createCaseList([
            'organisation_uuid' => $organisation->uuid,
            'is_queue' => true,
        ]);
        $this->createCaseForOrganisation($organisation, [
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        $repositoryResult = $this->caseListRepository->getCaseListByUuid($caseList->uuid, false);
        $this->assertEquals($caseList->uuid, $repositoryResult->uuid);
        $this->assertArrayNotHasKey('assigned_cases_count', $repositoryResult->getAttributes());
    }

    public function testGetCaseListByUuidWithStats(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));
        $caseList = $this->createCaseList([
            'organisation_uuid' => $organisation->uuid,
            'is_queue' => true,
        ]);
        $this->createCaseForOrganisation($organisation, [
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        $repositoryResult = $this->caseListRepository->getCaseListByUuid($caseList->uuid, true);
        $this->assertEquals($caseList->uuid, $repositoryResult->uuid);
        $this->assertArrayHasKey('assigned_cases_count', $repositoryResult->getAttributes());
    }
}
