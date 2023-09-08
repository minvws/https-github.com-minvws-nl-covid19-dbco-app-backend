<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Job;
use Generator;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

use function array_keys;
use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-job')]
class ApiCaseJobControllerTest extends FeatureTestCase
{
    /**
     * Test job fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/job');
        $this->assertStatus($response, 200);

        $response = $this->be($user)->get('/api/cases/' . Uuid::uuid4() . '/fragments/job');
        $this->assertStatus($response, 404);
    }

    /**
     * Test job fragment save.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no fields required
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/job', []);
        $this->assertStatus($response, 200);

        // store data
        $payload = [
            'wasAtJob' => YesNoUnknown::yes()->value,
            'sectors' => [JobSector::ziekenhuis()->value, JobSector::horeca()->value, JobSector::andereBeroep()->value],
            'professionCare' => ProfessionCare::verzorger()->value,
            'closeContactAtJob' => YesNoUnknown::yes()->value,
            'particularities' => 'particularities',
        ];

        $testFieldsInResponse = function (array $fieldsInResponse) use ($payload): void {
            foreach (array_keys($payload) as $fieldName) {
                $this->assertEquals(
                    $payload[$fieldName],
                    $fieldsInResponse[$fieldName],
                    sprintf('Failed asserting that field %s is in response.', $fieldName),
                );
            }
        };

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/job', $payload);
        $this->assertStatus($response, 200);
        $testFieldsInResponse($response->json('data'));

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/job');
        $this->assertStatus($response, 200);
        $testFieldsInResponse($response->json('data'));
    }

    /**
     * Test encode / decode job
     */
    public function testEncodeDecode(): void
    {
        $job = Job::newInstanceWithVersion(1);
        $job->wasAtJob = YesNoUnknown::yes();
        $job->sectors = [JobSector::ziekenhuis()];
        $job->professionCare = ProfessionCare::verzorger();
        $job->closeContactAtJob = YesNoUnknown::yes();
        $job->professionOther = ProfessionOther::anders();
        $job->otherProfession = 'Test';
        $job->particularities = 'particularities';

        // care sector should save professionCare
        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $encoded = $encoder->encode($job);
        $this->assertIsArray($encoded);

        $result = $job->getSchemaVersion()->validate($encoded);
        $this->assertTrue($result->isValid());

        $decoded = (new Decoder())->decode($encoded)->decodeObject(Job::class);
        $this->assertEquals($job, $decoded);
    }

    #[DataProvider('encodeSectorProfessionCareProvider')]
    public function testEncodeProfessionCareCondition(
        JobSector $sector,
        ProfessionCare $professionCare,
        ?string $expectedProfessionCare,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // store data
        $payload = [
            'wasAtJob' => YesNoUnknown::yes()->value,
            'sectors' => [$sector->value],
            'professionCare' => $professionCare->value,
        ];

        // setEncodingCondition is set to store, so in the POST response, the conditions are not applied
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/job', $payload);
        $this->assertStatus($response, 200);

        // but when refreshing the fragment, the conditions should be applied
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/job');
        $this->assertStatus($response, 200);
        $data = $response->json('data');

        $this->assertSame($expectedProfessionCare, $data['professionCare']);
    }

    public static function encodeSectorProfessionCareProvider(): Generator
    {
        yield 'if sector ziekenhuis > professionCare is visible' => [
            JobSector::ziekenhuis(),
            ProfessionCare::dietist(),
            ProfessionCare::dietist()->value,
        ];

        yield 'if sector andereZorg > professionCare is visible' => [
            JobSector::andereZorg(),
            ProfessionCare::dietist(),
            ProfessionCare::dietist()->value,
        ];

        yield 'if sector dagopvang > professionCare is not visible' => [
            JobSector::dagopvang(),
            ProfessionCare::dietist(),
            null,
        ];
    }

    #[DataProvider('encodeSectorContactBeroepProvider')]
    public function testEncodeContactBeroepCondition(
        JobSector $sector,
        YesNoUnknown $closeContactAtJob,
        ProfessionOther $professionOther,
        ?string $otherProfession,
        ?string $expectedProfessionCare,
        ?string $expectedProfessionOther,
        ?string $expectedOtherProfession,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // store data
        $payload = [
            'wasAtJob' => YesNoUnknown::yes()->value,
            'sectors' => [$sector->value],
            'closeContactAtJob' => $closeContactAtJob->value,
            'professionOther' => $professionOther->value,
            'otherProfession' => $otherProfession,
        ];

        // setEncodingCondition is set to store, so in the POST response, the conditions are not applied
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/job', $payload);
        $this->assertStatus($response, 200);

        // but when refreshing the fragment, the conditions should be applied
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/job');
        $this->assertStatus($response, 200);
        $data = $response->json('data');

        $this->assertSame($expectedProfessionCare, $data['closeContactAtJob']);
        $this->assertSame($expectedProfessionOther, $data['professionOther']);
        $this->assertSame($expectedOtherProfession, $data['otherProfession']);
    }

    public static function encodeSectorContactBeroepProvider(): Generator
    {
        yield 'if sector siekenhuis && closeContact > professionOther is removed' => [
            JobSector::ziekenhuis(),
            YesNoUnknown::yes(),
            ProfessionOther::kapper(),
            null,
            null,
            null,
            null,
        ];

        yield 'if sector anderBeroep && closeContact > professionOther is not removed' => [
            JobSector::andereBeroep(),
            YesNoUnknown::yes(),
            ProfessionOther::anders(),
            null,
            YesNoUnknown::yes()->value,
            ProfessionOther::anders()->value,
            null,
        ];

        yield 'if sector anderBeroep && closeContact is true and proffession is anders > otherProfession is not removed' => [
            JobSector::andereBeroep(),
            YesNoUnknown::yes(),
            ProfessionOther::anders(),
            'boer',
            YesNoUnknown::yes()->value,
            ProfessionOther::anders()->value,
            'boer',
        ];

        yield 'if sector anderBeroep && closeContact is true and proffession is not anders > otherProfession is removed' => [
            JobSector::andereBeroep(),
            YesNoUnknown::yes(),
            ProfessionOther::kapper(),
            'boer',
            YesNoUnknown::yes()->value,
            ProfessionOther::kapper()->value,
            null,
        ];
    }
}
