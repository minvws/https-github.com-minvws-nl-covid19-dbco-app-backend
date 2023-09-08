<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function route;

#[Group('case-connected')]
#[Group('task-connected')]
class ApiCaseAndTaskConnectedControllerTest extends FeatureTestCase
{
    public function testConnected(): void
    {
        $now = CarbonImmutable::now();

        $user = $this->createUser([], 'user,planner');
        $case = $this->createCaseForUser($user, [
            'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772',
            'created_at' => $now,
        ]);

        $matchingCaseOne = $this->createCaseForUser(
            $user,
            ['case_id' => '1234567', 'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772', 'created_at' => $now],
        );
        $matchingCaseTwo = $this->createCaseForUser(
            $user,
            ['case_id' => '3456789', 'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772', 'created_at' => $now->subDay()],
        );
        $this->createCaseForUser(
            $user,
            ['case_id' => '9999999', 'pseudo_bsn_guid' => '00000000-0000-0000-0000-000000000000', 'created_at' => $now],
        );

        $response = $this->be($user)->getJson(route('api-get-cases-connected', $case->uuid));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'cases');
        $response->assertJsonCount(0, 'tasks');
        $response->assertJson([
            'cases' => [
                [
                    'uuid' => $matchingCaseOne->uuid,
                    'organisation' => [
                        'abbreviation' => null,
                    ],
                    'number' => '1234567',
                    'dateOfSymptomOnset' => null,
                ],
                [
                    'uuid' => $matchingCaseTwo->uuid,
                    'organisation' => [
                        'abbreviation' => null,
                    ],
                    'number' => '3456789',
                    'dateOfSymptomOnset' => null,
                ],
            ],
            'tasks' => [],
        ]);
    }

    public function testConnectedWithTasks(): void
    {
        $now = CarbonImmutable::now();

        // organisation 1 has a matching case
        $organisation1 = $this->createOrganisation([
            'name' => 'GGD Test 1',
            'phone_number' => '456',
        ]);
        $userForOrganisation1 = $this->createUserForOrganisation($organisation1, [], 'user,planner');
        $matchingCaseForOrganisation1 = $this->createCaseForUser($userForOrganisation1, [
            'case_id' => '1234567',
            'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772',
            'created_at' => $now->copy()->subSeconds(5),
            'organisation_uuid' => $organisation1->uuid,
        ]);
        $nonMatchingCaseForOrganisation1 = $this->createCaseForUser($userForOrganisation1, [
            'case_id' => '9999999',
            'pseudo_bsn_guid' => Str::uuid(),
            'created_at' => $now->copy()->subSeconds(4),
            'organisation_uuid' => $organisation1->uuid,
        ]);

        // organisation 2 has a matching task
        $organisation2 = $this->createOrganisation([
            'name' => 'GGD Test 2',
            'phone_number' => '456',
        ]);
        $userForOrganisation2 = $this->createUserForOrganisation($organisation2, [], 'user,planner');
        $matchingCaseForOrganisation2 = $this->createCaseForUser($userForOrganisation2, [
            'case_id' => '1234568',
            'pseudo_bsn_guid' => Str::uuid(),
            'created_at' => $now->copy()->subSeconds(3),
            'organisation_uuid' => $organisation2->uuid,
        ]);
        $matchingTaskForOrganisation2 = $this->createTaskForCase($matchingCaseForOrganisation2, [
            'category' => ContactCategory::cat1(),
            'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772',
            'date_of_last_exposure' => $now->copy()->subSeconds(3),
            'created_at' => $now,
        ]);
        $nonMatchingCaseForOrganisation2 = $this->createCaseForUser($userForOrganisation2, [
            'case_id' => '1234569',
            'pseudo_bsn_guid' => Str::uuid(),
            'created_at' => $now->copy()->subSeconds(2),
            'organisation_uuid' => $organisation2->uuid,
        ]);
        $nonMatchingTaskForOrganisation2 = $this->createTaskForCase($nonMatchingCaseForOrganisation2, [
            'category' => ContactCategory::cat2a(),
            'pseudo_bsn_guid' => Str::uuid(),
            'date_of_last_exposure' => $now->copy()->subSeconds(2),
            'created_at' => $now,
        ]);

        // organisation 3 has a matching case
        $organisation3 = $this->createOrganisation([
            'name' => 'GGD Test 3',
            'phone_number' => '456',
        ]);
        $userForOrganisation3 = $this->createUserForOrganisation($organisation3, [], 'user,planner');
        $matchingCaseForOrganisation3 = $this->createCaseForUser($userForOrganisation3, [
            'case_id' => '2234567',
            'pseudo_bsn_guid' => '8d00a816-9976-43b4-a0d8-6d39dc907772',
            'created_at' => $now->copy()->subSeconds(1),
            'organisation_uuid' => $organisation3->uuid,
        ]);

        // test as organisation 1
        $response = $this->be($userForOrganisation1)->getJson(route('api-get-cases-connected', $matchingCaseForOrganisation1->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'cases');
        $response->assertJsonCount(1, 'tasks');
        $response->assertJson([
            'cases' => [
                [
                    'uuid' => $matchingCaseForOrganisation3->uuid,
                    'organisation' => [
                        'uuid' => $organisation3->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => $matchingCaseForOrganisation3->case_id,
                    'dateOfSymptomOnset' => null,
                ],
            ],
            'tasks' => [
                [
                    'uuid' => $matchingTaskForOrganisation2->uuid,
                    'organisation' => [
                        'uuid' => $organisation2->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => $matchingCaseForOrganisation2->case_id,
                    'category' => '1',
                    'dateOfLastExposure' => $now->format('Y-m-d'),
                    'relationship' => null,
                ],
            ],
        ]);

        $response = $this->be($userForOrganisation1)->getJson(route('api-get-cases-connected', $nonMatchingCaseForOrganisation1->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'cases');
        $response->assertJsonCount(0, 'tasks');

        // test as organisation 2
        $response = $this->be($userForOrganisation2)->getJson(route('api-get-cases-connected', $matchingCaseForOrganisation2->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'cases');
        $response->assertJsonCount(0, 'tasks');

        $response = $this->be($userForOrganisation2)->getJson(route('api-get-tasks-connected', $matchingTaskForOrganisation2->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'cases');
        $response->assertJsonCount(0, 'tasks');
        $response->assertJson([
            'cases' => [
                [
                    'uuid' => $matchingCaseForOrganisation3->uuid,
                    'organisation' => [
                        'uuid' => $organisation3->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => $matchingCaseForOrganisation3->case_id,
                    'dateOfSymptomOnset' => null,
                ],
                [
                    'uuid' => $matchingCaseForOrganisation1->uuid,
                    'organisation' => [
                        'uuid' => $organisation1->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => $matchingCaseForOrganisation1->case_id,
                    'dateOfSymptomOnset' => null,
                ],
            ],
        ]);


        $response = $this->be($userForOrganisation2)->getJson(route('api-get-tasks-connected', $nonMatchingTaskForOrganisation2->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'cases');
        $response->assertJsonCount(0, 'tasks');

        // test as organisation 3
        $response = $this->be($userForOrganisation3)->getJson(route('api-get-cases-connected', $matchingCaseForOrganisation3->uuid));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'cases');
        $response->assertJsonCount(1, 'tasks');
        $response->assertJson([
            'cases' => [
                [
                    'uuid' => $matchingCaseForOrganisation1->uuid,
                    'organisation' => [
                        'uuid' => $organisation1->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => '1234567',
                    'dateOfSymptomOnset' => null,
                ],
            ],
            'tasks' => [
                [
                    'uuid' => $matchingTaskForOrganisation2->uuid,
                    'organisation' => [
                        'uuid' => $organisation2->uuid,
                        'abbreviation' => null,
                    ],
                    'number' => '1234568',
                    'category' => '1',
                    'dateOfLastExposure' => $now->format('Y-m-d'),
                    'relationship' => null,
                ],
            ],
        ]);
    }

    public function testConnectedNoPseudoBsn(): void
    {
        $user = $this->createUser([], 'user,planner');
        $case = $this->createCaseForUser($user, [
            'pseudo_bsn_guid' => null,
        ]);

        $response = $this->be($user)->getJson(route('api-get-cases-connected', $case->uuid));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['error' => 'Case has no PseudoBSN']);
    }
}
