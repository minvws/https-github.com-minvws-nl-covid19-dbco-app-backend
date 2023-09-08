<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\FixedCalendarPeriod;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\CreateFragments;

use function array_filter;
use function sprintf;

#[Group('fragment')]
class ApiCaseFragmentControllerTest extends FeatureTestCase
{
    use CreateFragments;

    #[DataProvider('updateContactFragmentValidationDataProvider')]
    public function testUpdateContactFragmentValidation(
        array $postData,
        array $expectedContactData,
        ?array $expectedValidationResult,
        ?string $expectedEmailAddress,
        ?string $expectedPhone,
    ): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = null;
                    $test->dateOfTest = null;
                },
            ),
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments', $case->uuid), $postData);
        $response->assertStatus(200);
        $this->assertEquals($expectedContactData, $response->json()['data']['contact']);
        if ($expectedValidationResult) {
            $this->assertEquals($expectedValidationResult, $response->json()['validationResult']);
        }

        $case->refresh();
        $this->assertEquals($case->contact->email, $expectedEmailAddress);
        $this->assertEquals($case->contact->phone, $expectedPhone);
    }

    public static function updateContactFragmentValidationDataProvider(): array
    {
        return [
            'valid' => [
                [
                    'contact' => [
                        'schemaVersion' => 1,
                        'phone' => '0612345678',
                        'email' => 'foo@bar.com',
                    ],
                ],
                [
                    'schemaVersion' => 1,
                    'phone' => '06 12345678',
                    'email' => 'foo@bar.com',
                ],
                null,
                'foo@bar.com',
                '06 12345678',
            ],
            'invalid phone' => [
                [
                    'contact' => [
                        'schemaVersion' => 1,
                        'phone' => 'foo',
                        'email' => 'foo@bar.com',
                    ],
                ],
                [
                    'schemaVersion' => 1,
                    'phone' => null,
                    'email' => 'foo@bar.com',
                ],
                [
                    'contact' => [
                        'warning' => [
                            'failed' => [
                                'phone' => [
                                    'Phone' => [
                                        'INTERNATIONAL',
                                        'NL',
                                    ],
                                ],
                            ],
                            'errors' => [
                                'phone' => [
                                    'Veld "Telefoonnummer" moet een geldig telefoonnummer zijn.',
                                ],
                            ],
                        ],
                    ],
                ],
                'foo@bar.com',
                null,
            ],
            'invalid email' => [
                [
                    'contact' => [
                        'schemaVersion' => 1,
                        'phone' => '0612345678',
                        'email' => 'foo',
                    ],
                ],
                [
                    'schemaVersion' => 1,
                    'phone' => '06 12345678',
                    'email' => null,
                ],
                [
                    'contact' => [
                        'warning' => [
                            'failed' => [
                                'email' => [
                                    'Email' => [
                                        'filter',
                                    ],
                                ],
                            ],
                            'errors' => [
                                'email' => [
                                    'Veld "E-mailadres" is geen geldig e-mailadres.',
                                ],
                            ],
                        ],
                    ],
                ],
                null,
                '06 12345678',
            ],
        ];
    }

    #[Group('policy')]
    public function testUpdateCaseFragmentShouldAlsoReturnComputedCalendarData(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = CarbonImmutable::create(2021, 5, 27);
                    $test->dateOfTest = null;
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);

        $dateOfSymptomOnset = CarbonImmutable::create(2021, 5, 27);

        $response = $this
            ->be($user)
            ->putJson(sprintf('/api/cases/%s/fragments', $case->uuid), [
                'test' => [
                    'schemaVersion' => 1,
                    'dateOfSymptomOnset' => $dateOfSymptomOnset->format('Y-m-d'),
                ],
            ])
            ->assertStatus(200);

        $this->assertArrayHasKey('calendarData', $response->json()['computedData']);
        $this->assertArrayHasKey('calendarViews', $response->json()['computedData']);
        $computedDataPoints = array_filter(
            $response->json()['computedData']['calendarData'],
            static fn($data) => $data['type'] === CalendarItemEnum::point()->value
        );
        $this->assertCount(1, $computedDataPoints);
    }

    #[Group('policy')]
    public function testUpdateCaseFragmentComputedCalendarDataShouldContainPoints(): void
    {
        $user = $this->createUser();
        $dateOfSymptomOnset = CarbonImmutable::create(2021, 5, 27);
        $dateOfTest = CarbonImmutable::create(2021, 6, 01);
        $case = $this->createCaseForUser($user, [
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test) use ($dateOfSymptomOnset, $dateOfTest): void {
                    $test->dateOfSymptomOnset = $dateOfSymptomOnset;
                    $test->dateOfTest = $dateOfTest;
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments', $case->uuid), [
            'test' => [
                'schemaVersion' => 1,
                'dateOfSymptomOnset' => $dateOfSymptomOnset->format('Y-m-d'),
            ],
        ]);
        $response->assertStatus(200);

        $computedDataPoints = array_filter(
            $response->json()['computedData']['calendarData'],
            static fn($data) => $data['type'] === CalendarItemEnum::point()->value
        );
        $this->assertCount(2, $computedDataPoints);
    }

    #[Group('policy')]
    public function testGetCaseFragmentWithoutDateOfSymptomsOnsetAndDateOfTestShouldReturnEpisodePeriodData(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::create(2021, 5, 27),
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = null;
                    $test->dateOfTest = null;
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);

        $expectedCalendarData = [
            [
                'id' => 'episode',
                'type' => 'period',
                'startDate' => CarbonImmutable::create(2021, 5, 10)->format('Y-m-d'),
                'endDate' => CarbonImmutable::create(2021, 6, 13)->format('Y-m-d'),
                'key' => FixedCalendarPeriod::episode()->value,
                'label' => null,
                'color' => null,
            ],
        ];

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/fragments', $case->uuid));
        $response->assertStatus(200);
        $this->assertEquals($expectedCalendarData, $response->json()['computedData']['calendarData']);

        $this->assertArrayHasKey('calendarData', $response->json()['computedData']);
        $this->assertArrayHasKey('calendarViews', $response->json()['computedData']);
    }
}
