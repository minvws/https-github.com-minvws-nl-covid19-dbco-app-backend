<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Test;
use Carbon\CarbonImmutable;
use DateTime;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

use function collect;
use function config;
use function str_repeat;

#[Group('case-fragment')]
#[Group('case-fragment-general')]
class ApiCaseGeneralControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        $response = $this->get('/api/cases/' . $case->uuid . '/fragments/general');
        $this->assertStatus($response, 200);

        $pairingAllowedInterval = config('misc.case.pairingAllowedInterval');
        $this->assertTrue($pairingAllowedInterval > 1); // make sure the value is set to something

        $data = $response->json('data');
        $this->assertEquals(true, $data['isPairingAllowed']);
        $this->assertNotNull($data['createdAt']);
        $this->assertNotNull($data['pairingAllowedUntil']);
        $createdAt = new DateTime($data['createdAt']);
        $pairingAllowedUntil = new DateTime($data['pairingAllowedUntil']);
        $this->assertTrue($pairingAllowedUntil > $createdAt);
        $this->assertEquals($createdAt->getTimestamp() + $pairingAllowedInterval, $pairingAllowedUntil->getTimestamp());

        $response = $this->get('/api/cases/' . Uuid::uuid4() . '/fragments/general');
        $this->assertStatus($response, 404);
    }

    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        // check required fields
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', []);
        $this->assertStatus($response, 400);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('reference', $data['validationResult']['fatal']['errors']);

        // check storage
        $hpzoneNumber = '1234567';
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => $hpzoneNumber,
            'notes' => 'This is a note',
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertEquals($hpzoneNumber, $data['data']['hpzoneNumber']);
        $this->assertEquals('This is a note', $data['data']['notes']);

        // check partial update
        $hpzoneNumber2 = '2345678';
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => $hpzoneNumber2,
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertEquals($hpzoneNumber2, $data['data']['hpzoneNumber']);
        $this->assertEquals('This is a note', $data['data']['notes']);
        $this->assertFalse(isset($data['validationResult']['warning']));

        // check if really stored
        $response = $this->get('/api/cases/' . $case->uuid . '/fragments/general');
        $data = $response->json();
        $this->assertEquals($hpzoneNumber2, $data['data']['hpzoneNumber']);
        $this->assertEquals('This is a note', $data['data']['notes']);

        // check partial update that results in a warning
        $newNotes = str_repeat('a', 5001);
        $hpzoneNumber3 = '8888888';
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => $hpzoneNumber3,
            'notes' => $newNotes,
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertEquals($hpzoneNumber3, $data['data']['hpzoneNumber']);
        $this->assertEquals('This is a note', $data['data']['notes']); // should not be updated
        $this->assertTrue(isset($data['validationResult']['warning']));

        // check if the data from the warning is *not* really stored, but the successful data is
        $response = $this->get('/api/cases/' . $case->uuid . '/fragments/general');
        $data = $response->json();
        $this->assertEquals($hpzoneNumber3, $data['data']['hpzoneNumber']);
        $this->assertEquals('This is a note', $data['data']['notes']); // should not be updated
    }

    public function testPostUniqueCheckShouldNotFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        $hpzoneNumber = '1234567';
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => $hpzoneNumber,
            'notes' => 'This is a note',
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();

        $this->assertEquals($hpzoneNumber, $data['data']['hpzoneNumber']);

        //Save same reference a second time. Unique check should be ignored for the case
        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => $hpzoneNumber,
        ]);

        $this->assertStatus($response, 200);
    }

    public function testPostWithExistingHpzoneIdShouldFail(): void
    {
        $user = $this->createUser();
        $case1 = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $case2 = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        $reference = '1234567';
        $response = $this->putJson('/api/cases/' . $case1->uuid . '/fragments/general', [
            'reference' => $case1->caseId,
            'hpzoneNumber' => $reference,
            'notes' => 'This is a note',
        ]);

        $this->assertStatus($response, 200);

        $response = $this->putJson('/api/cases/' . $case2->uuid . '/fragments/general', [
            'reference' => $case2->caseId,
            'hpzoneNumber' => $reference,
            'notes' => 'This is a note',
        ]);

        $this->assertStatus($response, 400);
        $errors = collect($response->json())->flatten()->all();
        $this->assertContains('Er bestaat al een case met dit HPZone nummer', $errors);
    }

    public function testPostEmptyHpzoneShouldBeAllowedIfMonsterNrIsSet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);
        $case->general->hpzoneNumber = '1234567';
        $case->test = new Test();
        $case->test->monsterNumber = '123A4567';
        $case->save();

        $this->be($user);

        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => null,
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();

        $this->assertNull($data['data']['hpzoneNumber']);
    }

    #[DataProvider('validOptionalPropertiesWithValue')]
    public function testPostOptionalPropertyShouldNotFailV1(string $property, ?bool $value): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'schema_version' => 1,
            'test_monster_number' => '123A4567',
        ]);

        $this->be($user);

        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
            'hpzoneNumber' => '1234567',
            $property => $value,
        ]);
        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertEquals($value, $data['data'][$property]);
    }

    public static function validOptionalPropertiesWithValue(): Generator
    {
        yield 'with askedAboutCoronaMelder = TRUE' => [
            'askedAboutCoronaMelder',
            true,
        ];

        yield 'with askedAboutCoronaMelder = FALSE' => [
            'askedAboutCoronaMelder',
            false,
        ];

        yield 'with askedAboutCoronaMelder = NULL' => [
            'askedAboutCoronaMelder',
            null,
        ];
    }

    public function testPostOptionalPropertyShouldNotFailV2(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'schema_version' => 2,
        ]);

        $this->be($user);

        $response = $this->putJson('/api/cases/' . $case->uuid . '/fragments/general', [
            'reference' => $case->caseId,
        ]);
        $this->assertStatus($response, 200);
    }
}
