<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_intersect;
use function array_keys;

#[Group('context-fragments')]
final class ApiContextCircumstancesFragmentTest extends FeatureTestCase
{
    public function testGetSingleFragmentRetrieval(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);

        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/circumstances');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('isUsingPPE', $data['data']);
        $this->assertEquals(null, $data['data']['isUsingPPE']);
        $this->assertArrayHasKey('ppeMedicallyCompetent', $data['data']);
        $this->assertEquals(null, $data['data']['ppeMedicallyCompetent']);
    }

    public function testNonExistingFragmentRetrieval(): void
    {
        $this->be($this->createUser());

        $response = $this->get('/api/contexts/nonexisting/fragments/circumstances');
        $response->assertStatus(404);

        $response = $this->get('/api/contexts/nonexisting/fragments/?names=circumstances,contact');
        $response->assertStatus(404);
    }

    public function testGetMultiFragmentRetrieval(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/?names=circumstances,contact');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('circumstances', $data['data']);
        $this->assertArrayHasKey('isUsingPPE', $data['data']['circumstances']);
        $this->assertEquals(null, $data['data']['circumstances']['isUsingPPE']);
        $this->assertArrayHasKey('ppeMedicallyCompetent', $data['data']['circumstances']);
        $this->assertEquals(null, $data['data']['circumstances']['ppeMedicallyCompetent']);
        $this->assertArrayHasKey('contact', $data['data']);
        $this->assertArrayHasKey('firstname', $data['data']['contact']);
        $this->assertEquals(null, $data['data']['contact']['firstname']);

        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(3, array_intersect(['general', 'contact', 'circumstances'], array_keys($data['data'])));
    }

    public function testPutSingleFragmentNoFieldsRequired(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);

        // no fields required
        $response = $this->putJson(
            '/api/contexts/' . $context->uuid . '/fragments/circumstances',
            [],
        );
        $response->assertStatus(200);
    }

    public function testPutSingleFragmentStorage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);

        // check storage
        $response = $this->putJson('/api/contexts/' . $context->uuid . '/fragments/circumstances', [
            'isUsingPPE' => YesNoUnknown::yes(),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['isUsingPPE']);

        // check if really stored
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/circumstances');
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['isUsingPPE']);
    }

    public function testPutMultiFragmentStorage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);

        $response = $this->putJson('/api/contexts/' . $context->uuid . '/fragments/', [
            'circumstances' => [
                'isUsingPPE' => YesNoUnknown::yes(),
            ],
            'contact' => [
                'firstname' => 'John',
            ],
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['circumstances']['isUsingPPE']);
        $this->assertEquals('John', $data['data']['contact']['firstname']);

        // check if really stored
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/?fragmentNames=circumstances,contact');
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['circumstances']['isUsingPPE']);
        $this->assertEquals('John', $data['data']['contact']['firstname']);
    }

    public function testCircumstancesFragmentOtherCovidMeasuresMaxLength(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $this->be($user);

        $otherMeasures = [];
        for ($i = 0; $i < 5; $i++) {
            $otherMeasures[] = Str::random(300); //too long
        }

        $response = $this->putJson('/api/contexts/' . $context->uuid . '/fragments/', [
            'circumstances' => [
                'otherCovidMeasures' => $otherMeasures,
            ],
        ]);
        $response->assertStatus(400);
        $data = $response->json();
        $this->assertArrayHasKey('Max', $data['validationResult']['circumstances']['fatal']['failed']['otherCovidMeasures.0']);
    }
}
