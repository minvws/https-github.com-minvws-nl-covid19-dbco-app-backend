<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Illuminate\Http\Response;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiCaseSyncTest extends FeatureTestCase
{
    private EloquentUser $user;
    private EloquentCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $this->user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->case = $this->createCaseForUser($this->user);
    }

    public function testGetCaseFragments(): void
    {
        $response = $this->be($this->user)->getJson(sprintf('/api/sync/%s/fragments', $this->case->uuid));

        $response->assertJsonStructure([
            'uuid',
            'hpZoneNumber',
        ]);
        $response->assertJsonStructure([
            'fragments' => [
                'abroad',
                'alternateContact',
                'alternateResidency',
                'alternativeLanguage',
                'contacts',
                'deceased',
                'eduDaycare',
                'general',
                'generalPractitioner',
                'groupTransport',
                'hospital',
                'housemates',
                'index',
                'job',
                'medication',
                'pregnancy',
                'principalContextualSettings',
                'recentBirth',
                'riskLocation',
                'symptoms',
                'test',
                'underlyingSuffering',
                'vaccination',
                'communication',
                'immunity',
                'extensiveContactTracing',
                'sourceEnvironments',
                'generalPractitioner',
            ],
        ]);
        $response->assertJsonFragment([
            'uuid' => $this->case->uuid,
            'hpZoneNumber' => $this->case->hpZoneNumber,
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetCaseFragmentsWithoutPlannerRole(): void
    {
        $this->user->roles = 'user';

        $response = $this->getJson(sprintf('/api/sync/%s/fragments', $this->case->uuid));
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetCaseFragmentsWithDifferentOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $this->user->organisations()->sync([$organisation->uuid]);

        $response = $this->getJson(sprintf('/api/sync/%s/fragments', $this->case->uuid));
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetCaseFragmentsWithInvalidUuid(): void
    {
        $response = $this->be($this->user)->getJson(sprintf('/api/sync/%s/fragments', $this->faker->randomNumber()));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
