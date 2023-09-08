<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_encode;
use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-symptoms')]
class ApiCaseSymptomsControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/symptoms');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/symptoms');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/symptoms', []);
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/symptoms', [
            'hasSymptoms' => YesNoUnknown::yes(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasSymptoms']);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/symptoms');
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['hasSymptoms']);
    }

    public function testIndexSubmittedSymptomsTakenIntoAccount(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'index_submitted_at' => CarbonImmutable::now(),
            'index_submitted_symptoms' => json_encode([
                'headache',
                'diarrhea',
            ]),
        ]);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/symptoms', $case->uuid));
        $response->assertStatus(200);
        $symptoms = $response->json('data');

        $this->assertEquals('yes', $symptoms['hasSymptoms']);
    }

    public function testIndexWithoutSubmittedSymptomsTakenIntoAccount(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'index_submitted_at' => CarbonImmutable::now(),
            'index_submitted_symptoms' => json_encode([]),
        ]);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/symptoms', $case->uuid));
        $response->assertStatus(200);
        $symptoms = $response->json('data');

        $this->assertEquals('no', $symptoms['hasSymptoms']);
    }

    public function testMissingIndexSubmittedSymptomsShouldNotOverwriteExistingFragmentValues(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/symptoms', [
            'hasSymptoms' => YesNoUnknown::yes()->value,
        ]);
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/symptoms');
        $response->assertStatus(200);
        $symptoms = $response->json('data');

        $this->assertEquals('yes', $symptoms['hasSymptoms']);
    }

    public function testNewCaseWithNoIndexSubmissionShouldReturnNullForHasSymptoms(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/symptoms');
        $response->assertStatus(200);
        $symptoms = $response->json('data');

        $this->assertNull($symptoms['hasSymptoms']);
    }
}
