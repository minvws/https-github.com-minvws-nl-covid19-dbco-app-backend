<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-index')]
class ApiCaseIndexControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/index');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/index');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // check required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/index');
        $response->assertStatus(400);

        // check storage
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/index', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'dateOfBirth' => '2021-09-20',
            'bsnNotes' => 'bsn notes',
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('John', $data['data']['firstname']);
        $this->assertEquals('Doe', $data['data']['lastname']);
        $this->assertEquals('bsn notes', $data['data']['bsnNotes']);

        /** @var EloquentCase $case */
        $case = EloquentCase::find($case->uuid);
        $this->assertEquals('John Doe', $case->name);
        $this->assertNotNull($case->search_date_of_birth);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/index');
        $data = $response->json();
        $this->assertEquals('John', $data['data']['firstname']);
        $this->assertEquals('bsn notes', $data['data']['bsnNotes']);
    }
}
