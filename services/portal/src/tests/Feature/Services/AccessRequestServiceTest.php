<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\CovidCase\Job;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\Eloquent\Place;
use App\Models\Task\General;
use App\Models\Versions\CovidCase\Job\JobV1;
use App\Services\AccessRequestService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function config;
use function json_decode;
use function json_encode;

#[Group('access-request')]
class AccessRequestServiceTest extends FeatureTestCase
{
    private AccessRequestService $accessRequestService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessRequestService = app(AccessRequestService::class);
    }

    public function testCollectFragmentData(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020));

        $user = $this->createUser([], 'user', [
            'name' => 'Demo organisatie',
        ]);
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'case_id' => 'dummy',
            'hpzoneNumber' => '1234567',
            'date_of_symptom_onset' => new CarbonImmutable('3 days ago'),
            'date_of_test' => CarbonImmutable::yesterday(),
            'abroad' => null,
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                2,
                static function (UnderlyingSuffering $underlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = null;
                    $underlyingSuffering->hasUnderlyingSuffering = null;
                },
            ),
        ]);

        $caseFragmentData = (string) json_encode($this->accessRequestService->collectCaseFragmentData($case));

        $expectedCaseFragmentData = [
            'general' => [
                'source' => null,
                'reference' => 'dummy',
                'hpzoneNumber' => '1234567',
                'createdAt' => '2020-01-01 01:00:00',
                'organisation' => 'Demo organisatie',
                'deletedAt' => null,
            ],
            'index' => [
                'firstname' => null,
                'initials' => null,
                'lastname' => null,
                'dateOfBirth' => null,
                'gender' => null,
                'address' => null,
                'bsnCensored' => null,
                'bsnLetters' => null,
                'bsnNotes' => null,
                'hasNoBsnOrAddress' => null,
            ],
            'contact' => [
                'email' => null,
                'phone' => null,
            ],
            'alternateContact' => [
                'hasAlternateContact' => null,
            ],
            'alternativeLanguage' => [
                'useAlternativeLanguage' => null,
            ],
            'deceased' => [
                'isDeceased' => null,
            ],
            'symptoms' => [
                'hasSymptoms' => null,
            ],
            'test' => [
                'dateOfSymptomOnset' => '2019-12-29T00:00:00Z',
                'isSymptomOnsetEstimated' => false,
                'dateOfTest' => '2019-12-31T00:00:00Z',
                'dateOfResult' => null,
                'dateOfInfectiousnessStart' => null,
                'reasons' => null,
                'otherReason' => null,
                'monsterNumber' => null,
                'isReinfection' => null,
                'infectionIndicator' => null,
            ],
            'vaccination' => [
                'isVaccinated' => null,
            ],
            'hospital' => [
                'isAdmitted' => null,
            ],
            'underlyingSuffering' => [
                'hasUnderlyingSufferingOrMedication' => null,
                'hasUnderlyingSuffering' => null,
            ],
            'pregnancy' => [
                'isPregnant' => null,
            ],
            'recentBirth' => [],
            'medication' => [
                'hasMedication' => null,
                'isImmunoCompromised' => null,
            ],
            'generalPractitioner' => [
                'name' => null,
                'practiceName' => null,
                'address' => null,
                'hasInfectionNotificationConsent' => null,
            ],
            'alternateResidency' => [
                'hasAlternateResidency' => null,
            ],
            'housemates' => [
                'hasHouseMates' => null,
            ],
            'riskLocation' => [
                'isLivingAtRiskLocation' => null,
            ],
            'job' => [
                'wasAtJob' => null,
            ],
            'eduDaycare' => [],
            'principalContextualSettings' => [
                'hasPrincipalContextualSettings' => null,
            ],
            'abroad' => [
                'wasAbroad' => null,
            ],
            'contacts' => [
                'shareNameWithContacts' => null,
            ],
            'groupTransport' => [
                'withReservedSeats' => null,
            ],
            'sourceEnvironments' => [],
            'communication' => [
                'particularities' => null,
                'isolationAdviceGiven' => null,
            ],
            'immunity' => [],
            'extensiveContactTracing' => [
                'receivesExtensiveContactTracing' => null,
            ],
        ];

        $this->assertEquals($expectedCaseFragmentData, json_decode($caseFragmentData, true));
    }

    public function testCollectFragmentDataWithFilledData(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020));

        /** @var JobV1 $jobFragment */
        $jobFragment = Job::getSchema()->getVersion(1)->newInstance();
        $jobFragment->wasAtJob = YesNoUnknown::yes();
        $jobFragment->sectors = [JobSector::andereBeroep(), JobSector::ziekenhuis()];
        $jobFragment->professionCare = ProfessionCare::arts();
        $jobFragment->closeContactAtJob = YesNoUnknown::yes();
        $jobFragment->professionOther = ProfessionOther::anders();
        $jobFragment->otherProfession = 'Lorem Ipsum';

        $user = $this->createUser([], 'user', [
            'name' => 'Demo organisatie',
        ]);
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'case_id' => 'dummy',
            'hpzone_number' => null,
            'date_of_symptom_onset' => new CarbonImmutable('3 days ago'),
            'date_of_test' => CarbonImmutable::yesterday(),
            'abroad' => null,
            'job' => $jobFragment,
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                2,
                static function (UnderlyingSuffering $underlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = null;
                    $underlyingSuffering->hasUnderlyingSuffering = null;
                },
            ),
        ]);

        $caseFragmentData = (string) json_encode($this->accessRequestService->collectCaseFragmentData($case));

        $expectedCaseFragmentData = [
            'general' => [
                'source' => null,
                'reference' => 'dummy',
                'hpzoneNumber' => null,
                'createdAt' => '2020-01-01 01:00:00',
                'organisation' => 'Demo organisatie',
                'deletedAt' => null,
            ],
            'index' => [
                'firstname' => null,
                'initials' => null,
                'lastname' => null,
                'dateOfBirth' => null,
                'gender' => null,
                'address' => null,
                'bsnCensored' => null,
                'bsnLetters' => null,
                'bsnNotes' => null,
                'hasNoBsnOrAddress' => null,
            ],
            'contact' => [
                'email' => null,
                'phone' => null,
            ],
            'alternateContact' => [
                'hasAlternateContact' => null,
            ],
            'alternativeLanguage' => [
                'useAlternativeLanguage' => false,
            ],
            'deceased' => [
                'isDeceased' => null,
            ],
            'symptoms' => [
                'hasSymptoms' => null,
            ],
            'test' => [
                'dateOfSymptomOnset' => '2019-12-29T00:00:00Z',
                'isSymptomOnsetEstimated' => false,
                'dateOfTest' => '2019-12-31T00:00:00Z',
                'dateOfResult' => null,
                'dateOfInfectiousnessStart' => null,
                'reasons' => null,
                'otherReason' => null,
                'monsterNumber' => null,
                'isReinfection' => null,
                'infectionIndicator' => null,
            ],
            'vaccination' => [
                'isVaccinated' => null,
            ],
            'hospital' => [
                'isAdmitted' => null,
            ],
            'underlyingSuffering' => [
                'hasUnderlyingSufferingOrMedication' => null,
                'hasUnderlyingSuffering' => null,
            ],
            'pregnancy' => [
                'isPregnant' => null,
            ],
            'recentBirth' => [],
            'medication' => [
                'hasMedication' => null,
                'isImmunoCompromised' => null,
            ],
            'generalPractitioner' => [
                'name' => null,
                'practiceName' => null,
                'address' => null,
                'hasInfectionNotificationConsent' => null,
            ],
            'alternateResidency' => [
                'hasAlternateResidency' => null,
            ],
            'housemates' => [
                'hasHouseMates' => null,
            ],
            'riskLocation' => [
                'isLivingAtRiskLocation' => null,
            ],
            'job' => [
                'wasAtJob' => 'Ja',
                'sectors' => ['Ander beroep', 'Ziekenhuis'],
                'professionCare' => 'Arts',
                'closeContactAtJob' => 'Ja',
                'professionOther' => 'Anders',
                'otherProfession' => 'Lorem Ipsum',
                'particularities' => null,
            ],
            'eduDaycare' => [],
            'principalContextualSettings' => [
                'hasPrincipalContextualSettings' => null,
            ],
            'abroad' => [
                'wasAbroad' => null,
            ],
            'contacts' => [
                'shareNameWithContacts' => null,
            ],
            'groupTransport' => [
                'withReservedSeats' => null,
            ],
            'sourceEnvironments' => [],
            'communication' => [
                'isolationAdviceGiven' => null,
                'particularities' => null,
            ],
            'immunity' => [],
            'extensiveContactTracing' => [
                'receivesExtensiveContactTracing' => null,
            ],
        ];

        $this->assertEquals($expectedCaseFragmentData, json_decode($caseFragmentData, true));
    }

    public function testCollectContactData(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020));

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat2a(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'Martha';
                $general->lastname = 'Kent';
                $general->email = 'martha.kent@kansas.dc';
                $general->phone = '555-123145667';
            }),
            'date_of_last_exposure' => CarbonImmutable::yesterday(),
            'task_context' => 'Task progress test',
            'nature' => 'Ze heeft morgen en test',
        ]);

        $expectedCaseContactData = [
            [
                'general' => [
                    'reference' => null,
                    'firstname' => 'Martha',
                    'lastname' => 'Kent',
                    'email' => 'martha.kent@kansas.dc',
                    'phone' => '555-123145667',
                    'dateOfLastExposure' => '2019-12-31T00:00:00Z',
                    'category' => '2A - Nauw contact (Opgeteld meer dan 15 minuten binnen 1,5 meter)',
                    'isSource' => false,
                    'label' => 'Martha Kent',
                    'context' => 'Task progress test',
                    'relationship' => null,
                    'otherRelationship' => null,
                    'closeContactDuringQuarantine' => null,
                    'nature' => 'Ze heeft morgen en test',
                    'deletedAt' => null,
                ],
                'circumstances' => [
                    'wasUsingPPE' => null,
                ],
            ],
        ];

        $caseContactData = (string) json_encode($this->accessRequestService->collectCaseContactData($case));
        $this->assertEquals($expectedCaseContactData, json_decode($caseContactData, true));
    }

    public function testCollectContactDataWithNonAccessibleTask(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020));
        config()->set('misc.encryption.task_availability_in_days', 30);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat2a(),
            'task_type' => 'contact',
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'Martha';
                $general->lastname = 'Kent';
                $general->email = 'martha.kent@kansas.dc';
                $general->phone = '555-123145667';
            }),
            'date_of_last_exposure' => CarbonImmutable::yesterday(),
            'task_context' => 'Task progress test',
            'nature' => 'Ze heeft morgen en test',
        ]);

        $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now()->subYear(),
            'category' => ContactCategory::cat2a(),
            'task_type' => 'contact',
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'Martha';
                $general->lastname = 'Kent';
                $general->email = 'martha.kent@kansas.dc';
                $general->phone = '555-123145667';
            }),
            'date_of_last_exposure' => CarbonImmutable::yesterday(),
            'task_context' => 'Task progress test',
            'nature' => 'Ze heeft morgen een test',
        ]);

        $expectedCaseContactData = [
            [
                'general' => [
                    'dateOfLastExposure' => '2019-12-31',
                    'label' => null,
                    'category' => '2a',
                    'nature' => 'Ze heeft morgen een test',
                    'deletedAt' => null,
                    'isSource' => false,
                    'availabilityNote' => 'automatisch verwijderd na 30 dagen',
                ],
            ],
            [
                'general' => [
                    'reference' => null,
                    'firstname' => 'Martha',
                    'lastname' => 'Kent',
                    'email' => 'martha.kent@kansas.dc',
                    'phone' => '555-123145667',
                    'dateOfLastExposure' => '2019-12-31T00:00:00Z',
                    'category' => '2A - Nauw contact (Opgeteld meer dan 15 minuten binnen 1,5 meter)',
                    'isSource' => false,
                    'label' => 'Martha Kent',
                    'context' => 'Task progress test',
                    'relationship' => null,
                    'otherRelationship' => null,
                    'closeContactDuringQuarantine' => null,
                    'nature' => 'Ze heeft morgen en test',
                    'deletedAt' => null,
                ],
                'circumstances' => [
                    'wasUsingPPE' => null,
                ],
            ],
        ];

        $caseContactData = (string) json_encode($this->accessRequestService->collectCaseContactData($case));
        $this->assertEquals($expectedCaseContactData, json_decode($caseContactData, true));
    }

    public function testCollectContextData(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2020));

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        /** @var Place $place */
        $place = Place::factory()->create();

        $context1 = $this->createContextForCase($case, [
            'uuid' => '1',
            'label' => 'Voetbalvereniging Delfgauw',
            'relationship' => ContextRelationship::staff(),
            'place_uuid' => $place->uuid,
            'is_source' => false,
        ]);
        $this->createMomentForContext($context1, [
            'day' => '2020-10-07',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $context2 = $this->createContextForCase($case, [
            'uuid' => '2',
            'label' => 'Tennis',
            'relationship' => ContextRelationship::staff(),
            'place_uuid' => $place->uuid,
            'is_source' => false,
        ]);
        $this->createMomentForContext($context2, [
            'day' => '2020-10-07',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context2, [
            'day' => '2020-10-08',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context2, [
            'day' => '2020-10-09',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $context3 = $this->createContextForCase($case, [
            'uuid' => '3',
            'label' => 'Albert Heijn Goudsesingel',
            'relationship' => ContextRelationship::staff(),
            'place_uuid' => $place->uuid,
            'is_source' => false,
        ]);
        $this->createMomentForContext($context3, [
            'day' => '2020-10-02',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context3, [
            'day' => '2020-10-09',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context3, [
            'day' => '2020-10-11',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context3, [
            'day' => '2020-10-12',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $context4 = $this->createContextForCase($case, [
            'uuid' => '4',
            'label' => 'Bakkerij Bart Arnhem',
            'relationship' => ContextRelationship::staff(),
            'place_uuid' => $place->uuid,
            'is_source' => false,
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-09-26',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-09-28',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-09-29',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-11-01',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-11-02',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-11-03',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
        $this->createMomentForContext($context4, [
            'day' => '2020-11-05',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $expectedCaseContextData = [
            [
                'general' => [
                    'label' => 'Voetbalvereniging Delfgauw',
                    'relationship' => 'Medewerker',
                    'otherRelationship' => null,
                    'isSource' => false,
                    'moments' => [
                        '2020-10-07 (12:00 - 13:00)',
                    ],
                ],
                'circumstances' => [
                    'isUsingPPE' => null,
                    'covidMeasures' => null,
                    'otherCovidMeasures' => null,
                    'causeForConcern' => null,
                    'sharedTransportation' => null,
                ],
                'contact' => [
                    'firstname' => null,
                    'lastname' => null,
                    'phone' => null,
                    'isInformed' => null,
                    'notificationConsent' => null,
                    'notificationNamedConsent' => null,
                ],
            ],
            [
                'general' => [
                    'label' => 'Tennis',
                    'relationship' => 'Medewerker',
                    'otherRelationship' => null,
                    'isSource' => false,
                    'moments' => [
                        '2020-10-07 (12:00 - 13:00)',
                        '2020-10-08 (12:00 - 13:00)',
                        '2020-10-09 (12:00 - 13:00)',
                    ],
                ],
                'circumstances' => [
                    'isUsingPPE' => null,
                    'covidMeasures' => null,
                    'otherCovidMeasures' => null,
                    'causeForConcern' => null,
                    'sharedTransportation' => null,
                ],
                'contact' => [
                    'firstname' => null,
                    'lastname' => null,
                    'phone' => null,
                    'isInformed' => null,
                    'notificationConsent' => null,
                    'notificationNamedConsent' => null,
                ],
            ],
            [
                'general' => [
                    'label' => 'Albert Heijn Goudsesingel',
                    'relationship' => 'Medewerker',
                    'otherRelationship' => null,
                    'isSource' => false,
                    'moments' => [
                        '2020-10-02 (12:00 - 13:00)',
                        '2020-10-09 (12:00 - 13:00)',
                        '2020-10-11 (12:00 - 13:00)',
                        '2020-10-12 (12:00 - 13:00)',
                    ],
                ],
                'circumstances' => [
                    'isUsingPPE' => null,
                    'covidMeasures' => null,
                    'otherCovidMeasures' => null,
                    'causeForConcern' => null,
                    'sharedTransportation' => null,
                ],
                'contact' => [
                    'firstname' => null,
                    'lastname' => null,
                    'phone' => null,
                    'isInformed' => null,
                    'notificationConsent' => null,
                    'notificationNamedConsent' => null,
                ],
            ],
            [
                'general' => [
                    'label' => 'Bakkerij Bart Arnhem',
                    'relationship' => 'Medewerker',
                    'otherRelationship' => null,
                    'isSource' => false,
                    'moments' => [
                        '2020-09-26 (12:00 - 13:00)',
                        '2020-09-28 (12:00 - 13:00)',
                        '2020-09-29 (12:00 - 13:00)',
                        '2020-11-01 (12:00 - 13:00)',
                        '2020-11-02 (12:00 - 13:00)',
                        '2020-11-03 (12:00 - 13:00)',
                        '2020-11-05 (12:00 - 13:00)',
                    ],
                ],
                'circumstances' => [
                    'isUsingPPE' => null,
                    'covidMeasures' => null,
                    'otherCovidMeasures' => null,
                    'causeForConcern' => null,
                    'sharedTransportation' => null,
                ],
                'contact' => [
                    'firstname' => null,
                    'lastname' => null,
                    'phone' => null,
                    'isInformed' => null,
                    'notificationConsent' => null,
                    'notificationNamedConsent' => null,
                ],
            ],
        ];

        $caseContextData = (string) json_encode($this->accessRequestService->collectContextData($case));
        $this->assertEquals($expectedCaseContextData, json_decode($caseContextData, true));
    }
}
