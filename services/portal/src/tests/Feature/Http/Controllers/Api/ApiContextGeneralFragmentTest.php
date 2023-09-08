<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_encode;
use function sprintf;

#[Group('context-fragments')]
final class ApiContextGeneralFragmentTest extends FeatureTestCase
{
    public function testGetGeneralFragment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case, [
            'label' => 'foo',
            'relationship' => ContextRelationship::resident(),
            'other_relationship' => 'not your father',
            'remarks' => 'some remarks',
            'explanation' => 'another note',
            'is_source' => false,
        ]);

        $response = $this->be($user)->getJson(sprintf('api/contexts/%s/fragments/general', $context->uuid));
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponseData = (object) [
            'data' => [
                'schemaVersion' => 1,
                'label' => 'foo',
                'relationship' => 'resident',
                'otherRelationship' => 'not your father',
                'remarks' => 'some remarks',
                'note' => 'another note',
                'isSource' => false,
                'moments' => [],
            ],
        ];
        $this->assertEquals(json_encode($expectedResponseData), $response->getContent());
    }

    public function testGetGeneralFragmentWithInvalidTimes(): void
    {
        $today = CarbonImmutable::today();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $today->format('Y-m-d'),
            'date_of_test' => $today->modify('-1 day')->format('Y-m-d'),
        ]);
        $context = $this->createContextForCase($case, [
            'label' => 'foo',
            'relationship' => ContextRelationship::resident(),
            'other_relationship' => 'not your father',
            'remarks' => 'some remarks',
            'explanation' => 'another note',
            'is_source' => false,
        ]);
        $weekBeforeToday = $today->subDays(7); //stay within source-period

        $this->createMomentForContext($context, [
            'day' => $weekBeforeToday->format('Y-m-d'),
            'start_time' => '-10:00:00',
            'end_time' => '-00:00:01',
        ]);

        $response = $this->be($user)->getJson(sprintf('api/contexts/%s/fragments/general', $context->uuid));
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponseData = (object) [
            'data' => [
                'schemaVersion' => 1,
                'label' => 'foo',
                'relationship' => 'resident',
                'otherRelationship' => 'not your father',
                'remarks' => 'some remarks',
                'note' => 'another note',
                'isSource' => false,
                'moments' => [
                    [
                        'schemaVersion' => 1,
                        'day' => $weekBeforeToday->format('Y-m-d'),
                        'startTime' => null,
                        'endTime' => null,
                        'source' => null,
                        'formatted' => null,
                    ],
                ],
            ],
        ];

        $this->assertEquals(json_encode($expectedResponseData), $response->getContent());
    }

    public function testPutGeneralFragment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->putJson(sprintf('api/contexts/%s/fragments/general', $context->uuid), [
            'isSource' => false,
            'label' => 'my label',
            'moments' => [],
            'note' => 'some note',
            'remarks' => 'lorum ipsum',
            'otherRelationship' => 'I am your father',
            'relationship' => 'teacher',
            'schemaVersion' => 1,
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponseData = (object) [
            'data' => [
                'schemaVersion' => 1,
                'label' => 'my label',
                'relationship' => 'teacher',
                'otherRelationship' => 'I am your father',
                'remarks' => 'lorum ipsum',
                'note' => 'some note',
                'isSource' => false,
                'moments' => [],
            ],
        ];
        $this->assertEquals(json_encode($expectedResponseData), $response->getContent());
    }

    public function testPutGeneralFragmentWithInvalidTimes(): void
    {
        $today = CarbonImmutable::today();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $today->format('Y-m-d'),
            'date_of_test' => $today->modify('-1 day')->format('Y-m-d'),
        ]);
        $context = $this->createContextForCase($case);

        $weekBeforeToday = $today->subDays(7); //stay within source-period
        $response = $this->be($user)->putJson(sprintf('api/contexts/%s/fragments/general', $context->uuid), [
            'isSource' => false,
            'label' => 'my label',
            'moments' => [
                [
                    'day' => $weekBeforeToday->format('Y-m-d'),
                    'startTime' => 'foo',
                    'endTime' => 'bar',
                ],
            ],
            'note' => 'some note',
            'remarks' => 'lorum ipsum',
            'otherRelationship' => 'I am your father',
            'relationship' => 'teacher',
            'schemaVersion' => 1,
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        $expectedResponseData = [
            'validationResult' => [
                'fatal' => [
                    'failed' => [
                        'moments.0.startTime' => [
                            'DateFormat' => ['H:i'],
                        ],
                        'moments.0.endTime' => [
                            'DateFormat' => ['H:i'],
                            'After' => ['moments.0.startTime'],
                        ],
                    ],
                    'errors' => [
                        'moments.0.startTime' => ['Veld "Van" moet een geldig datum formaat bevatten.'],
                        'moments.0.endTime' => [
                            'Veld "Tot" moet een geldig datum formaat bevatten.',
                            'De tot-tijd moet na de vanaf-tijd zijn.',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedResponseData, $response->json());
    }

    public function testGeneralFragmentLoadingAndStorage(): void
    {
        $today = CarbonImmutable::today();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $today->format('Y-m-d'),
            'date_of_test' => $today->modify('-1 day')->format('Y-m-d'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();
        $context = $this->createContextForCase($case);

        $this->be($user);

        // check if data returned is based on context
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/general');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('label', $data['data']);
        $this->assertArrayHasKey('isSource', $data['data']);
        $this->assertArrayHasKey('relationship', $data['data']);
        $this->assertArrayHasKey('note', $data['data']);
        $this->assertEquals($context->label, $data['data']['label']);
        $this->assertEquals($context->is_source, $data['data']['isSource']);
        $this->assertEquals($context->relationship, $data['data']['relationship']);
        $this->assertEquals($context->explanation, $data['data']['note']);
        $this->assertEquals(0, $context->moments()->count());
        $this->assertCount(0, $data['data']['moments']);

        // update some data
        $firstMomentDate = $today->modify('-1 day')->format('Y-m-d');
        $secondMomentDate = $today->format('Y-m-d');
        $response = $this->putJson('/api/contexts/' . $context->uuid . '/fragments/general', [
            'label' => 'Blaat',
            'isSource' => true,
            'moments' => [
                [
                    'day' => $firstMomentDate,
                    'startTime' => '09:00',
                    'endTime' => '12:00',
                ],
                [
                    'day' => $secondMomentDate,
                    'startTime' => '09:30',
                    'endTime' => '15:15',
                ],
            ],
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('Blaat', $data['data']['label']);
        $this->assertTrue($data['data']['isSource']);
        $this->assertCount(2, $data['data']['moments']);
        $this->assertEquals($firstMomentDate, $data['data']['moments'][0]['day']);
        $this->assertEquals('09:00', $data['data']['moments'][0]['startTime']);

        // really stored?
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/general');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('Blaat', $data['data']['label']);
        $this->assertTrue($data['data']['isSource']);
        $this->assertCount(2, $data['data']['moments']);
        $this->assertEquals($firstMomentDate, $data['data']['moments'][0]['day']);
        $this->assertEquals('09:00', $data['data']['moments'][0]['startTime']);

        // also check entity
        $context->refresh();
        $this->assertEquals('Blaat', $context->label);
        $this->assertTrue($context->is_source);
        $this->assertEquals(2, $context->moments()->count());
        $this->assertEquals($firstMomentDate, $context->moments[0]->day->format('Y-m-d'));
        $this->assertEquals('09:00:00', $context->moments[0]->start_time);
    }

    public function testAddDateWithOnlyTimeShouldBeProhibited(): void
    {
        $today = CarbonImmutable::today();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $today->format('Y-m-d'),
            'date_of_test' => $today->modify('-1 day')->format('Y-m-d'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();
        $context = $this->createContextForCase($case);

        $this->be($user);

        $response = $this->putJson('/api/contexts/' . $context->uuid . '/fragments/general', [
            'label' => 'Blaat',
            'isSource' => true,
            'moments' => [
                [
                    'startTime' => '09:00',
                    'endTime' => '12:00',
                ],
            ],
        ]);
        $response->assertStatus(400);
        $data = $response->json();

        $this->assertArrayHasKey('Required', $data['validationResult']['fatal']['failed']['moments.0.day']);
    }

    #[Group('context-general-fragment')]
    public function testUpdateInContextShouldBeAvailableInGeneralFragment(): void
    {
        $today = CarbonImmutable::today();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $today->format('Y-m-d'),
            'date_of_test' => $today->modify('-1 day')->format('Y-m-d'),
        ]);

        $context = $this->createContextForCase($case);
        $context->label = "Blaat";
        $context->isSource = true;
        $context->relationship = ContextRelationship::patient();
        $context->explanation = "Just an explanation";

        $this->assertEquals($context->label, $context->general->label);
        $this->assertEquals($context->is_source, $context->general->isSource);
        $this->assertEquals($context->relationship, $context->general->relationship);
        $this->assertEquals($context->explanation, $context->general->note);

        $context->save();
        $context->refresh();

        $this->assertEquals($context->label, $context->general->label);
        $this->assertEquals($context->is_source, $context->general->isSource);
        $this->assertEquals($context->relationship, $context->general->relationship);
        $this->assertEquals($context->explanation, $context->general->note);

        $this->be($user);

        // check if data returned is based on context
        $response = $this->get('/api/contexts/' . $context->uuid . '/fragments/general');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($context->label, $data['data']['label']);
        $this->assertEquals($context->is_source, $data['data']['isSource']);
        $this->assertEquals($context->relationship, $data['data']['relationship']);
        $this->assertEquals($context->explanation, $data['data']['note']);
        $this->assertEquals(0, $context->moments()->count());
        $this->assertCount(0, $data['data']['moments']);
    }
}
