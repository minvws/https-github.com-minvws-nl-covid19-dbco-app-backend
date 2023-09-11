<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Tests\Feature\FeatureTestCase;

use function config;

class ApiCaseLockControllerTest extends FeatureTestCase
{
    public function testHasCaseLockShouldReturnNotFoundIfNoCaseLockActive(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);

        $response = $this->getJson('/api/case/' . $case->uuid . '/lock');
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testHasCaseLockShouldReturnTheCaseLockEncoderResponseIfCaseLockActiveForOtherUser(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $organisation = $this->createOrganisation();
        $otherUser = $this->createUserForOrganisation($organisation);
        $this->createCaseLockForCaseAndUser($case, $otherUser);

        $response = $this->getJson('/api/case/' . $case->uuid . '/lock');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'user' => [
                'name' => $otherUser->name,
                'organisation' => $organisation->name,
            ],
            'case' => [
                'uuid' => $case->uuid,
                'case_id' => $case->case_id,
            ],
        ]);
    }

    public function testHasCaseLockShouldReturnNotFoundIfCaseLockIsExpired(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCase($case, [
            'locked_at' => CarbonImmutable::now()->subMinutes(config('misc.caseLock.lifetime') + 1),
        ]);

        $response = $this->getJson('/api/case/' . $case->uuid . '/lock');
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testRefreshCaseLock(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $user);

        $response = $this->postJson('/api/case/' . $case->uuid . '/lock/refresh');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testRefreshCaseLockWillReturnNotFoundIfNoneExists(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);

        $response = $this->postJson('/api/case/' . $case->uuid . '/lock/refresh');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testRefreshCaseLockWillReturnNotFoundIfCaseLockExpires(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $user, [
            'locked_at' => CarbonImmutable::now()->subMinutes(config('misc.caseLock.lifetime') + 1),
        ]);

        $response = $this->postJson('/api/case/' . $case->uuid . '/lock/refresh');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testRefreshCaseLockWillReturnBadRequestIfCaseLockIsNotOwnedByUser(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $this->createUser());

        $response = $this->postJson('/api/case/' . $case->uuid . '/lock/refresh');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testRemoveCaseLock(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $user);

        $response = $this->deleteJson('/api/case/' . $case->uuid . '/lock/remove');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function testRemoveCaseLockWillReturnNotFoundIfNoneExists(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);

        $response = $this->deleteJson('/api/case/' . $case->uuid . '/lock/remove');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testRemoveCaseLockWillReturnNotFoundIfCaseLockExpires(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $user, [
            'locked_at' => CarbonImmutable::now()->subMinutes(config('misc.caseLock.lifetime') + 1),
        ]);

        $response = $this->deleteJson('/api/case/' . $case->uuid . '/lock/remove');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testRemoveCaseLockWillReturnBadRequestIfCaseLockIsNotOwnedByUser(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $this->createUser());

        $response = $this->deleteJson('/api/case/' . $case->uuid . '/lock/remove');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
