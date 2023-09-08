<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

#[Group('case-list')]
class ApiCaseListControllerTest extends FeatureTestCase
{
    public function testCaseListGet(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->getJson('/api/caselists/' . $caseList->uuid);
        $response->assertStatus(200);

        $this->assertEquals($caseList->uuid, $response->json('uuid'));
        $this->assertEquals('Dummy', $response->json('name'));
        $this->assertEquals(false, $response->json('isDefault'));
        $this->assertEquals(false, $response->json('isQueue'));
        $this->assertArrayNotHasKey('assignedCasesCount', $response->json());
        $this->assertArrayNotHasKey('unassignedCasesCount', $response->json());
        $this->assertArrayNotHasKey('completedCasesCount', $response->json());
        $this->assertArrayNotHasKey('archivedCasesCount', $response->json());
    }

    public function testCaseListGetWithStats(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->getJson('/api/caselists/' . $caseList->uuid . '?stats=1');
        $response->assertStatus(200);

        $this->assertEquals($caseList->uuid, $response->json('uuid'));
        $this->assertEquals('Dummy', $response->json('name'));
        $this->assertEquals(false, $response->json('isDefault'));
        $this->assertEquals(false, $response->json('isQueue'));
        $this->assertEquals(0, $response->json('assignedCasesCount'));
        $this->assertEquals(0, $response->json('unassignedCasesCount'));
        $this->assertEquals(0, $response->json('completedCasesCount'));
        $this->assertEquals(0, $response->json('archivedCasesCount'));
    }

    public function testCaseListGetValidation(): void
    {
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->getJson('/api/caselists/nonexisting');
        $response->assertStatus(404);
    }

    public function testCaseListList(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $this->createCaseList([
            'name' => 'Wachtrij',
            'is_queue' => true,
            'is_default' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $this->createCaseList([
            'name' => 'A',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $this->createCaseList([
            'name' => 'C',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $this->createCaseList([
            'name' => 'B',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->getJson('/api/caselists');
        $this->assertStatus($response, 200);
        $this->assertEquals(4, $response->json('total'));
        $this->assertEquals('Wachtrij', $response->json('data.0.name'));
        $this->assertEquals('A', $response->json('data.1.name'));
        $this->assertEquals('B', $response->json('data.2.name'));
        $this->assertEquals('C', $response->json('data.3.name'));

        $response = $this->be($user)->getJson('/api/caselists?types=list');
        $this->assertStatus($response, 200);
        $this->assertEquals(3, $response->json('total'));
        $this->assertEquals('A', $response->json('data.0.name'));
        $this->assertEquals('B', $response->json('data.1.name'));
        $this->assertEquals('C', $response->json('data.2.name'));

        $response = $this->be($user)->getJson('/api/caselists?types=queue');
        $this->assertStatus($response, 200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('Wachtrij', $response->json('data.0.name'));
    }

    #[DataProvider('createCaseListDataProvider')]
    public function testUpdateCreate(array $postData): void
    {
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->postJson('/api/caselists/', $postData);
        $response->assertStatus(201);
        $this->assertTrue(Uuid::isValid($response->json('uuid')));
        $this->assertEquals($postData['name'], $response->json('name'));
        $this->assertEquals(false, $response->json('isDefault'));
        $this->assertEquals(false, $response->json('isQueue'));
    }

    public static function createCaseListDataProvider(): array
    {
        return [
            'first list' => [['name' => 'My First List', 'isQueue' => false]],
            'second list' => [['name' => 'My Second List', 'isQueue' => false]],
        ];
    }

    #[DataProvider('createCaseListValidationDataProvider')]
    public function testUpdateCreateValidation(array $postData): void
    {
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->postJson('/api/caselists/', $postData);
        $response->assertStatus(422);
    }

    public static function createCaseListValidationDataProvider(): array
    {
        return [
            'name is required' => [['isQueue' => false]],
            'isQueue cannot be true' => [['name' => 'My First List', 'isQueue' => true]],
        ];
    }

    public function testCreateCaseListValidationNameShouldBeUnique(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $this->createCaseList([
            'name' => 'My First List',
            'is_queue' => true,
            'is_default' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->postJson('/api/caselists/', [
            'name' => 'My First List',
        ]);
        $response->assertStatus(422);
    }

    #[DataProvider('updateDataProvider')]
    public function testCaseListUpdate(array $postData, string $expectedName): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->putJson('/api/caselists/' . $caseList->uuid, $postData);
        $response->assertStatus(200);

        $this->assertEquals($expectedName, $response->json('name'));

        $caseList->refresh();
        $this->assertEquals($expectedName, $caseList->name);
    }

    public static function updateDataProvider(): array
    {
        return [
            'valid' => [['name' => 'Dummy updated'], 'Dummy updated'],
            'empty' => [[], 'Dummy'],
        ];
    }

    public function testCaseListUpdateNonExisting(): void
    {
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->putJson('/api/caselists/nonexisting', [
            'name' => 'Dummy',
        ]);
        $response->assertStatus(404);
    }

    #[DataProvider('updateValidationDataProvider')]
    public function testCaseListUpdateValidation(array $postData): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $this->createCaseList([
            'name' => 'Default',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        // isQueue is only allowed to be false for now
        $response = $this->be($user)->putJson('/api/caselists/' . $caseList->uuid, $postData);
        $response->assertStatus(422);
    }

    public static function updateValidationDataProvider(): array
    {
        return [
            'isQueue is only allowed to be false' => [['isQueue' => true]],
            'name should be unique' => [['name' => 'Default']],
        ];
    }

    public function testCaseListUpdateDefaultQueueCannotBeUpdated(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Default',
            'is_queue' => false,
            'is_default' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->putJson('/api/caselists/' . $caseList->uuid, [
            'name' => 'Default Updated',
        ]);
        $response->assertStatus(403);
    }

    public function testCaseListDelete(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->delete('/api/caselists/' . $caseList->uuid . '?force=1');
        $this->assertEquals(204, $response->status());
    }

    public function testCaseListDeleteNonExisting(): void
    {
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->delete('/api/caselists/nonexisting');
        $this->assertEquals(404, $response->status());
    }

    public function testCaseListDeletDefaultIsNotAllowed(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $response = $this->be($user)->delete('/api/caselists/' . $caseList->uuid);
        $this->assertEquals(403, $response->status());
    }

    public function testDeleteNonEmptyOnlyWithForce(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createCaseForOrganisation($organisation, [
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($user)->delete('/api/caselists/' . $caseList->uuid);
        $this->assertEquals(403, $response->status());

        $response = $this->be($user)->delete('/api/caselists/' . $caseList->uuid . '?force=1');
        $this->assertEquals(204, $response->status());
    }

    public function testDeleteNonEmptyClearsCases(): void
    {
        $user = $this->createUser([], 'planner');
        $organisation = $user->organisations->first();

        $caseList = $this->createCaseList([
            'name' => 'Dummy',
            'is_queue' => false,
            'is_default' => false,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $case = $this->createCaseForOrganisation($organisation, [
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($user)->delete('/api/caselists/' . $caseList->uuid . '?force=1');
        $this->assertEquals(204, $response->status());

        $case = EloquentCase::find($case->uuid);

        $this->assertNull($case->assignedCaseList);
        $this->assertNull($case->assignedCaseListUuid);
    }
}
