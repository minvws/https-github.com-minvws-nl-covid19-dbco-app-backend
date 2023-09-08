<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('planner-case')]
#[Group('planner-case-sort')]
class ApiPlannerCaseControllerSortOrderTest extends FeatureTestCase
{
    #[DataProvider('sortAndOrderDataProvider')]
    public function testPlannerCaseSortAndOrder(string $sort, string $order, string $expectedCaseId): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $case1 = $this->createCaseForOrganisation($organisation, [
            'uuid' => '1',
            'case_id' => '1',
            'created_at' => CarbonImmutable::create(2020, 4, 1),
            'updated_at' => CarbonImmutable::create(2020, 5, 1),
            'date_of_test' => CarbonImmutable::create(2020, 3, 1),
            'status_index_contact_tracing' => ContactTracingStatus::conversationStarted(),
            'bco_status' => BCOStatus::open(),
        ]);
        $case2 = $this->createCaseForOrganisation($organisation, [
            'uuid' => '2',
            'case_id' => '2',
            'created_at' => CarbonImmutable::create(2020, 2, 2),
            'updated_at' => CarbonImmutable::create(2020, 2, 1),
            'date_of_test' => CarbonImmutable::create(2020, 5, 1),
            'status_index_contact_tracing' => ContactTracingStatus::conversationStarted(),
            'bco_status' => BCOStatus::open(),
        ]);
        $case3 = $this->createCaseForOrganisation($organisation, [
            'uuid' => '3',
            'case_id' => '3',
            'created_at' => CarbonImmutable::create(2020, 3, 3),
            'updated_at' => CarbonImmutable::create(2020, 3, 1),
            'date_of_test' => CarbonImmutable::create(2020, 1, 1),
            'status_index_contact_tracing' => ContactTracingStatus::notReachable(),
            'bco_status' => BCOStatus::open(),
            'priority' => Priority::high(),
        ]);
        $case4 = $this->createCaseForOrganisation($organisation, [
            'uuid' => '4',
            'case_id' => '4',
            'created_at' => CarbonImmutable::create(2020, 5, 4),
            'updated_at' => CarbonImmutable::create(2020, 1, 1),
            'date_of_test' => CarbonImmutable::create(2020, 4, 1),
            'status_index_contact_tracing' => ContactTracingStatus::completed(),
            'bco_status' => BCOStatus::open(),
            'priority' => Priority::normal(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => '5',
            'case_id' => '5',
            'created_at' => CarbonImmutable::create(2020, 1, 5),
            'updated_at' => CarbonImmutable::create(2020, 4, 1),
            'date_of_test' => CarbonImmutable::create(2020, 2, 1),
            'status_index_contact_tracing' => ContactTracingStatus::notApproached(),
            'bco_status' => BCOStatus::open(),
        ]);

        $this->createTaskForCase($case1);
        $this->createTaskForCase($case1);
        $this->createTaskForCase($case2);
        $this->createTaskForCase($case3);
        $this->createTaskForCase($case4);
        $this->createTaskForCase($case4);
        $this->createTaskForCase($case4);

        $response = $this->be($user)->getJson(sprintf('/api/cases/unassigned/?sort=%s&order=%s', $sort, $order));

        $data = $response->json('data');
        $this->assertEquals($expectedCaseId, $data[0]['caseId']);
    }

    public static function sortAndOrderDataProvider(): array
    {
        return [
            'createdAt asc' => ['createdAt', 'asc', '5'],
            'createdAt desc' => ['createdAt', 'desc', '4'],
            'updatedAt asc' => ['updatedAt', 'asc', '4'],
            'updatedAt desc' => ['updatedAt', 'desc', '1'],
            'contactsCount asc' => ['contactsCount', 'asc', '5'],
            'contactsCount desc' => ['contactsCount', 'desc', '4'],
            'caseStatus asc' => ['caseStatus', 'asc', '4'],
            'caseStatus desc' => ['caseStatus', 'desc', '3'],
            'priority asc' => ['priority', 'asc', '5'],
            'priority desc' => ['priority', 'desc', '3'],
        ];
    }
}
