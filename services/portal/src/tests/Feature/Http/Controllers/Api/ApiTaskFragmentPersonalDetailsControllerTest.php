<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\PersonalDetails;
use App\Models\Task\TaskAddress;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\NoBsnOrAddressReason;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentPersonalDetailsControllerTest extends FeatureTestCase
{
    public function testGetPersonalDetailsEmptyData(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments/personalDetails', $task->uuid));
        $response->assertStatus(200);

        $expectedResponseData = [
            'data' => [
                'schemaVersion' => 2,
                'dateOfBirth' => null,
                'gender' => null,
                'address' => [
                    'schemaVersion' => 1,
                    'postalCode' => null,
                    'houseNumberSuffix' => null,
                    'street' => null,
                    'town' => null,
                    'houseNumber' => null,
                ],
                'bsnCensored' => null,
                'bsnLetters' => null,
                'bsnNotes' => null,
                'hasNoBsnOrAddress' => null,
            ],
        ];

        $this->assertEquals($expectedResponseData, $response->json());
    }

    public function testGetPersonalDetailsWithData(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $address = TaskAddress::newInstanceWithVersion(1);
        $address->street = 'foostreet';
        $address->houseNumber = '1';
        $address->postalCode = '1234AB';
        $address->town = 'footown';

        $personalDetails = PersonalDetails::newInstanceWithVersion(2);
        $personalDetails->dateOfBirth = CarbonImmutable::createFromDate(2000, 1, 1);
        $personalDetails->gender = Gender::female();
        $personalDetails->address = $address;
        $personalDetails->bsnCensored = '*****123';
        $personalDetails->bsnLetters = 'AB';
        $personalDetails->bsnNotes = 'foobar';
        $personalDetails->hasNoBsnOrAddress = [NoBsnOrAddressReason::homeless()->value];

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'personal_details' => $personalDetails,
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments/personalDetails', $task->uuid));
        $response->assertStatus(200);

        $expectedResponseData = [
            'data' => [
                'schemaVersion' => 2,
                'dateOfBirth' => '2000-01-01',
                'gender' => 'female',
                'address' => [
                    'schemaVersion' => 1,
                    'postalCode' => '1234AB',
                    'houseNumberSuffix' => null,
                    'street' => 'foostreet',
                    'town' => 'footown',
                    'houseNumber' => '1',
                ],
                'bsnCensored' => '*****123',
                'bsnLetters' => 'AB',
                'bsnNotes' => 'foobar',
                'hasNoBsnOrAddress' => ['homeless'],
            ],
        ];

        $this->assertEquals($expectedResponseData, $response->json());
    }

    public function testGetPersonalDetailsLoadsDateOfBirthQuestionnaireAnswer(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        /** @var PersonalDetails $personalDetails */
        $personalDetails = PersonalDetails::newInstanceWithVersion(1);
        $personalDetails->address = TaskAddress::newInstanceWithVersion(1);
        $personalDetails->dateOfBirth = CarbonImmutable::create(2021, 4, 18)->toDateTime();
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'personal_details' => $personalDetails,
        ]);
        $question = $this->createQuestion([
            'group_name' => 'contactdetails',
            'question_type' => 'date',
        ]);
        $this->createAnswerForTaskWithQuestion($task, $question, [
            'spv_value' => CarbonImmutable::instance(new DateTimeImmutable('2021-04-18T10:10:10'))->format('c'),
        ]);

        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/personalDetails');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('2021-04-18', $data['data']['dateOfBirth']);
    }

    public function testPostPersonalDetails(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $postData = [
            'dateOfBirth' => '2000-01-01',
            'gender' => 'male',
            'address' => [
                'postalCode' => '1234AB',
                'houseNumber' => '2',
                'street' => 'foo',
                'town' => 'bar',
            ],
            'bsnCensored' => '*****789',
            'bsnLetters' => 'YZ',
            'bsnNotes' => 'some notes',
        ];

        $uri = sprintf('/api/tasks/%s/fragments/personal-details', $task->uuid);
        $response = $this->be($user)->putJson($uri, $postData);
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);

        $expectedResponseData = [
            'data' => [
                'schemaVersion' => 2,
                'dateOfBirth' => '2000-01-01',
                'gender' => 'male',
                'address' => [
                    'schemaVersion' => 1,
                    'postalCode' => '1234AB',
                    'houseNumberSuffix' => null,
                    'street' => 'foo',
                    'town' => 'bar',
                    'houseNumber' => '2',
                ],
                'bsnCensored' => '*****789',
                'bsnLetters' => 'YZ',
                'bsnNotes' => 'some notes',
                'hasNoBsnOrAddress' => null,
            ],
        ];

        $this->assertEquals($expectedResponseData, $response->json());
    }
}
