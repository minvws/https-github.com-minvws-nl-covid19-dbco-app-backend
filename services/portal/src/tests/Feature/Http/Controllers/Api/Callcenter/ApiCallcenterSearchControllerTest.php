<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Callcenter;

use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Models\Task\TaskAddress;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('search-hash')]
#[Group('callcenter')]
class ApiCallcenterSearchControllerTest extends FeatureTestCase
{
    public function testSearchIndexSingleResult(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $case = $this->createCaseForOrganisation($user->getOrganisation(), [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $valueArray['dateOfBirth']->format('d-m-Y'),
                    'lastname' => $valueArray['lastname'],
                    'phone' => $valueArray['phone'],
                ],
            );

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(1, $body, 'Unexpected number of results');

        $firstResult = $body[0];
        $this->assertEquals($case->uuid, $firstResult['uuid'], 'Uuid does not match');
        $this->assertEquals(SearchHashResultType::index()->value, $firstResult['caseType'], 'Result type does not match');
        $this->assertTrue($firstResult['isMatch'], 'Result should be an match');
    }

    public function testSearchContactSingleResult(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $task = $this->createTaskForUser(
            $user,
            taskAttributes: [
                'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                    $general->lastname = $valueArray['lastname'];
                    $general->phone = $valueArray['phone'];
                }),
                'personalDetails' => PersonalDetails::newInstanceWithVersion(
                    1,
                    static function (PersonalDetails $personalDetails) use ($valueArray): void {
                        $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                    },
                ),
            ],
            caseAttributes: ['organisation_uuid' => $user->getOrganisation()->uuid],
        );

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $valueArray['dateOfBirth']->format('d-m-Y'),
                    'lastname' => $valueArray['lastname'],
                    'phone' => $valueArray['phone'],
                ],
            );

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(1, $body, 'Unexpected number of results');

        $firstResult = $body[0];
        $this->assertEquals($task->caseUuid, $firstResult['uuid'], 'Uuid does not match');
        $this->assertEquals(SearchHashResultType::contact()->value, $firstResult['caseType'], 'Result type does not match');
        $this->assertTrue($firstResult['isMatch'], 'Result should be an match');
    }

    public function testSearchIndexWithAddress(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'postalCode' => $this->faker->postcode(),
            'houseNumber' => $this->faker->buildingNumber(),
            'houseNumberSuffix' => null, //$this->faker->randomLetter(),
        ];

        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $case = $this->createCaseForOrganisation($user->getOrganisation(), [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
                $index->address = IndexAddress::newInstanceWithVersion(1, static function (IndexAddress $address) use ($valueArray): void {
                    $address->postalCode = $valueArray['postalCode'];
                    $address->houseNumber = $valueArray['houseNumber'];
                    $address->houseNumberSuffix = $valueArray['houseNumberSuffix'];
                });
            }),
        ]);

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $valueArray['dateOfBirth']->format('d-m-Y'),
                    'lastname' => $valueArray['lastname'],
                    'postalCode' => $valueArray['postalCode'],
                    'houseNumber' => $valueArray['houseNumber'],
                    'houseNumberSuffix' => $valueArray['houseNumberSuffix'],
                ],
            );

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(1, $body, 'Unexpected number of results');

        $firstResult = $body[0];
        $this->assertEquals($case->uuid, $firstResult['uuid'], 'Uuid does not match');
        $this->assertEquals(SearchHashResultType::index()->value, $firstResult['caseType'], 'Result type does not match');
        $this->assertTrue($firstResult['isMatch'], 'Result should be an match');
    }

    public function testSearchContactWithAddress(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'postalCode' => $this->faker->postcode(),
            'houseNumber' => $this->faker->buildingNumber(),
            'houseNumberSuffix' => null,
        ];

        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $task = $this->createTaskForUser(
            $user,
            taskAttributes: [
                'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                    $general->lastname = $valueArray['lastname'];
                }),
                'personalDetails' => PersonalDetails::newInstanceWithVersion(
                    1,
                    static function (PersonalDetails $personalDetails) use ($valueArray): void {
                        $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                        $personalDetails->address = TaskAddress::newInstanceWithVersion(
                            1,
                            static function (TaskAddress $address) use ($valueArray): void {
                                $address->postalCode = $valueArray['postalCode'];
                                $address->houseNumber = $valueArray['houseNumber'];
                                $address->houseNumberSuffix = $valueArray['houseNumberSuffix'];
                            },
                        );
                    },
                ),
            ],
            caseAttributes: ['organisation_uuid' => $user->getOrganisation()->uuid],
        );

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $valueArray['dateOfBirth']->format('d-m-Y'),
                    'lastname' => $valueArray['lastname'],
                    'postalCode' => $valueArray['postalCode'],
                    'houseNumber' => $valueArray['houseNumber'],
                    'houseNumberSuffix' => $valueArray['houseNumberSuffix'],
                ],
            );

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(1, $body, 'Unexpected number of results');

        $firstResult = $body[0];
        $this->assertEquals($task->caseUuid, $firstResult['uuid']);
        $this->assertEquals(SearchHashResultType::contact()->value, $firstResult['caseType']);
        $this->assertTrue($firstResult['isMatch']);
    }

    public function testSearchMultiResults(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $user = $this->createUserWithOrganisation(roles: 'callcenter');

        $caseUuids = [];
        $taskUuids = [];

        // As long as it is >1, so we are testing multiple returned results for each type:
        $amountOfEachType = 2;

        for ($i = 0; $i < $amountOfEachType; $i++) {
            $case = $this->createCaseForOrganisation($user->getOrganisation(), [
                'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                    $index->dateOfBirth = $valueArray['dateOfBirth'];
                    $index->lastname = $valueArray['lastname'];
                }),
                'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                    $contact->phone = $valueArray['phone'];
                }),
            ]);
            $caseUuids[] = $case->uuid;

            $task = $this->createTaskForUser(
                $user,
                taskAttributes: [
                    'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                        $general->lastname = $valueArray['lastname'];
                        $general->phone = $valueArray['phone'];
                    }),
                    'personalDetails' => PersonalDetails::newInstanceWithVersion(
                        1,
                        static function (PersonalDetails $personalDetails) use ($valueArray): void {
                            $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                        },
                    ),
                ],
                caseAttributes: ['organisation_uuid' => $user->getOrganisation()->uuid],
            );
            $taskUuids[] = $task->caseUuid;
        }

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $valueArray['dateOfBirth']->format('d-m-Y'),
                    'lastname' => $valueArray['lastname'],
                    'phone' => $valueArray['phone'],
                ],
            );

        $response->assertStatus(200);

        $body = Collection::make($response->json())->groupBy('caseType');

        // Check if we got the expected groups of results:
        $this->assertCount(
            2,
            $body,
            sprintf('We are expecting 2 types of results: index and contact, got: %s', $body->keys()->implode(', ')),
        );

        // Check case results
        $this->assertContains(SearchHashResultType::index()->value, $body->keys(), 'We are expecting index results');
        $this->assertCount(
            $amountOfEachType,
            $body->get(SearchHashResultType::index()->value),
            'We are expecting a specific number of index results',
        );

        foreach ($body->get(SearchHashResultType::index()->value)->pluck('uuid') as $uuid) {
            $this->assertContains($uuid, $caseUuids, 'We are expecting a specific index result');
        }

        // Check task results
        $this->assertContains(SearchHashResultType::contact()->value, $body->keys(), 'We are expecting contact results');
        $this->assertCount(
            $amountOfEachType,
            $body->get(SearchHashResultType::contact()->value),
            'We are expecting a specific number of contact results',
        );

        foreach ($body->get(SearchHashResultType::contact()->value)->pluck('uuid') as $uuid) {
            $this->assertContains($uuid, $taskUuids, 'We are expecting a specific contact result');
        }
    }

    public function testSearchIndexNoResult(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $this->createCaseForOrganisation($user->getOrganisation(), [
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween();
                $index->lastname = $this->faker->lastName();
            }),
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->phone = $this->faker->phoneNumber();
            }),
        ]);

        $response = $this
            ->be($user)
            ->post(
                '/api/callcenter/search',
                [
                    'dateOfBirth' => $this->faker->dateTimeBetween()->format('d-m-Y'),
                    'lastname' => $this->faker->lastName(),
                    'phone' => $this->faker->phoneNumber(),
                ],
            );

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertCount(0, $body, 'Unexpected number of results');
    }
}
