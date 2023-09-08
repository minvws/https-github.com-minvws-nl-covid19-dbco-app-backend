<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\TimezoneAware;
use App\Models\CovidCase\EduDaycare;
use App\Models\CovidCase\ExtensiveContactTracing;
use App\Models\CovidCase\Job;
use App\Models\CovidCase\Medication;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV1;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV3Up;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\BCOType;
use MinVWS\DBCO\Enum\Models\ExtensiveContactTracingReason;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

/**
 * Class ApiCopyDiagnosticsTest
 */
#[Group('copytables')]
final class ApiCopyDiagnosticsTest extends FeatureTestCase
{
    public function testCopyContainsSymptomInformation(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->symptoms->symptoms = [Symptom::fever()];
        $case->symptoms->diseaseCourse = 'Ziekteverloop';
        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeText("Klachten: Koorts (= boven 38 graden Celsius)");
        $response->assertSeeText("Ziekteverloop: Ziekteverloop");
    }

    public function testCopyContainsNotSymptomaticAtTimeOfCall(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->symptoms->symptoms = [Symptom::fever()];
        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeText("Klachten: Koorts (= boven 38 graden Celsius)");
    }

    public function testCopyContainsLabelWithContextPlace(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $place = Place::factory()->create([
            'label' => 'Juliana Torens',
        ]);

        $this->createContextForCase($case, [
            'label' => 'Pretparks',
            'place_uuid' => $place->uuid,
        ]);

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            'Clustering: Vermeld de naam van alle contexten en situations:',
            'Juliana Toren',
        ]);
    }

    public function testCopyContainsLabelWhenMissingContextPlace(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $this->createContextForCase($case, [
            'label' => 'Pretpark',
            'place_uuid' => null,
        ]);

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            'Clustering: Vermeld de naam van alle contexten en situations:',
            'Pretpark (niet gekoppeld)',
        ]);
    }

    #[DataProvider('copyContainsUnderlyingSufferingProvider')]
    public function testCopyContainsUnderlyingSuffering(
        ?callable $underlyingSufferingCallable,
        ?callable $medicationCallable,
        array $result = [],
    ): void {
        $user = $this->createUser();

        $underlyingSuffering = UnderlyingSuffering::newInstanceWithVersion(1, $underlyingSufferingCallable);
        $medication = $medicationCallable !== null ? Medication::newInstanceWithVersion(1, $medicationCallable) : null;
        $case = $this->createCaseForUser($user, [
            'underlying_suffering' => $underlyingSuffering,
            'medication' => $medication,
        ]);

        $this->createContextForCase($case);

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeInOrder($result);
    }

    /**
     * @return Generator<array>
     */
    public static function copyContainsUnderlyingSufferingProvider(): Generator
    {
        yield 'Yes, with remarks' => [
            static function (UnderlyingSuffering $underlyingSuffering): void {
                $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
            },
            static function (Medication $medication): void {
                $medication->isImmunoCompromised = YesNoUnknown::yes();
                $medication->immunoCompromisedRemarks = 'remarks';
            },
            [
                'Verminderde afweer:',
                'Ja, remarks',
            ],
        ];

        yield 'Yes, without remarks' => [
            static function (UnderlyingSuffering $underlyingSuffering): void {
                $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
            },
            static function (Medication $medication): void {
                $medication->isImmunoCompromised = YesNoUnknown::yes();
                $medication->immunoCompromisedRemarks = null;
            },
            [
                'Verminderde afweer:',
                'Ja',
            ],
        ];

        yield 'No, underlying suffering or medication' => [
            static function (UnderlyingSuffering $underlyingSuffering): void {
                $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::no();
            },
            null,
            [
                'Verminderde afweer:',
                'Nee',
            ],
        ];

        yield 'No, underlying suffering or medication yes, no immuno comprimised' => [
            static function (UnderlyingSuffering $underlyingSuffering): void {
                $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
            },
            static function (Medication $medication): void {
                $medication->isImmunoCompromised = YesNoUnknown::no();
            },
            [
                'Verminderde afweer:',
                'Nee',
            ],
        ];

        yield 'Unknown, underlying suffering or medication' => [
            static function (UnderlyingSuffering $underlyingSuffering): void {
                $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::unknown();
            },
            null,
            [
                'Verminderde afweer:',
                'Onbekend',
            ],
        ];

        yield 'Unknown, immuno comprimised unknown' => [
            null,
            static function (Medication $medication): void {
                $medication->isImmunoCompromised = YesNoUnknown::unknown();
            },
            [
                'Verminderde afweer:',
                'Onbekend',
            ],
        ];

        yield 'Unknown, all empty' => [
            null,
            null,
            [
                'Verminderde afweer:',
                'Onbekend',
            ],
        ];
    }

    #[DataProvider('jobSectorEventOptionsProvider')]
    #[Group('copyjob')]
    public function testCopyContainsJobSectorInformation(
        callable $jobCallable,
        ?callable $eduDaycareCallable,
        array $expectedTexts,
        array $unexpectedTexts = [],
    ): void {
        $user = $this->createUser();

        $eduDaycare = $eduDaycareCallable !== null ? EduDaycare::newInstanceWithVersion(2, $eduDaycareCallable) : null;

        $case = $this->createCaseForUser($user);
        $case->job = Job::newInstanceWithVersion(1, $jobCallable);
        $case->eduDaycare = $eduDaycare;
        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);
        $response->assertSeeTextInOrder($expectedTexts);

        if ($unexpectedTexts) {
            $response->assertDontSeeText($unexpectedTexts);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function jobSectorEventOptionsProvider(): Generator
    {
        yield 'Sector Ziekenhuis, Basisonderwijs' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
                $job->sectors = [JobSector::ziekenhuis()];
            },
            static function (EduDaycare $eduDaycare): void {
            },
            [
                'Werk, functie, stage, vrijwilligerswerk',
                'Ja,',
                'Sector werk: Ziekenhuis',
            ],
        ];

        yield 'Sector Onbekend, Basisonderwijs' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
            },
            static function (EduDaycare $eduDaycare): void {
            },
            [
                'Werk, functie, stage, vrijwilligerswerk',
                'Ja,',
                'Sector werk: Onbekend',
            ],
        ];

        yield 'Sector Onbekend, eduDaycare null' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
            },
            null,
            [
                'Werk, functie, stage, vrijwilligerswerk',
                'Ja,',
                'Sector werk: Onbekend',
            ],
        ];

        yield 'Sector Onbekend, eduDaycare Onbekend' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
            },
            static function (EduDaycare $eduDaycare): void {
            },
            [
                'Werk, functie, stage, vrijwilligerswerk',
                'Ja,',
                'Sector werk: Onbekend',
            ],
        ];

        yield 'wasAtJob: yes, with particularities' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
                $job->particularities = 'particularities';
            },
            null,
            ['Bijzonderheden: particularities'],
        ];

        yield 'wasAtJob: yes, without particularities' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
                $job->particularities = null;
            },
            null,
            [],
            ['Bijzonderheden: particularities'],
        ];

        yield 'wasAtJob: no - with particularities' => [
            static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::no();
                $job->particularities = 'particularities';
            },
            null,
            [],
            ['Bijzonderheden: particularities'],
        ];
    }

    public function testCopyContainsCaseCreatedAtInformation(): void
    {
        $user = $this->createUser();
        $createdAt = new CarbonImmutable();
        $case = $this->createCaseForUser($user, ['created_at' => $createdAt]);

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);

        $response->assertSeeTextInOrder(["Aanmaakdatum in BCO Portaal", TimezoneAware::format($createdAt, 'd-m-Y H:i')]);
    }

    #[DataProvider('extendedBCOV1OptionsProvider')]
    #[Group('copyjob')]
    public function testCopyContainsExtendedBcoInformationV1(
        callable $extensiveContactTracingCallable,
        array $expectedTexts,
        array $unexpectedTexts = [],
    ): void {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, ['schema_version' => 1]);
        $case->extensiveContactTracing = ExtensiveContactTracing::newInstanceWithVersion(1, $extensiveContactTracingCallable);
        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));
        $response->assertStatus(200);
        $response->assertSeeTextInOrder($expectedTexts);

        if ($unexpectedTexts) {
            $response->assertDontSeeText($unexpectedTexts);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function extendedBcoV1OptionsProvider(): Generator
    {
        yield 'Extensive contact tracing YES' => [
            static function (ExtensiveContactTracingV1 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = YesNoUnknown::yes();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
            },
            [
                'Uitgebreid BCO',
                'Ja',
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
            ],
        ];

        yield 'Extensive contact tracing NO' => [
            static function (ExtensiveContactTracingV1 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = YesNoUnknown::no();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
            },
            [
                'Uitgebreid BCO',
                'Nee',
            ],
            [
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
                'Elaborate BCO notes #123',
            ],
        ];

        yield 'Extensive contact tracing UNKNOWN' => [
            static function (ExtensiveContactTracingV1 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = YesNoUnknown::unknown();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
            },
            [
                'Uitgebreid BCO',
                'Onbekend',
            ],
            [
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
                'Elaborate BCO notes #123',
            ],
        ];
    }

    #[DataProvider('extendedBCOV2OptionsProvider')]
    #[Group('copyjob')]
    public function testCopyContainsExtendedBcoInformationV2(
        callable $extensiveContactTracingCallable,
        array $expectedTexts,
        array $unexpectedTexts = [],
    ): void {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, ['schema_version' => 4]);
        $case->extensiveContactTracing = ExtensiveContactTracing::newInstanceWithVersion(2, $extensiveContactTracingCallable);
        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));

        $response->assertStatus(200);
        $response->assertSeeTextInOrder($expectedTexts);

        if ($unexpectedTexts) {
            $response->assertDontSeeText($unexpectedTexts);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function extendedBCOV2OptionsProvider(): Generator
    {
        yield 'Extensive contact tracing EXTENSIVE, with options' => [
            static function (ExtensiveContactTracingV2 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::extensive();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Uitgebreid',
            ],
            [
                'This is my tracing description field',
            ],
        ];

        yield 'Extensive contact tracing EXTENSIVE, without options' => [
            static function (ExtensiveContactTracingV2 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::extensive();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = null;
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Uitgebreid',
            ],
            [
                'This is my tracing description field',
            ],
        ];

        yield 'Extensive contact tracing STANDARD' => [
            static function (ExtensiveContactTracingV2 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::standard();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Standaard',
            ],
            [
                'Toelichting: Elaborate BCO notes #123',
                'This is my tracing description field',
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
            ],
        ];

        yield 'Extensive contact tracing OTHER' => [
            static function (ExtensiveContactTracingV2 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::other();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Anders',
                'This is my tracing description field',
            ],
            [
                'Toelichting: Elaborate BCO notes #123',
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
            ],
        ];

        yield 'Extensive contact tracing UNKNOWN' => [
            static function (ExtensiveContactTracingV2 $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::unknown();
                $contactTracing->notes = 'Elaborate BCO notes #123';
                $contactTracing->reasons = [ExtensiveContactTracingReason::hardToReachGroup(), ExtensiveContactTracingReason::partKnownCluster()];
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Onbekend',
            ],
            [
                'This is my tracing description field',
                'Toelichting: Elaborate BCO notes #123',
                ExtensiveContactTracingReason::hardToReachGroup()->label,
                ExtensiveContactTracingReason::partKnownCluster()->label,
            ],
        ];
    }

    #[DataProvider('extendedBCOV3UpOptionsProvider')]
    #[Group('copyjob')]
    public function testCopyContainsExtendedBcoInformationV3Up(
        callable $extensiveContactTracingCallable,
        array $expectedTexts,
        array $unexpectedTexts = [],
    ): void {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, ['schema_version' => EloquentCase::getSchema()->getCurrentVersion()->getVersion()]);

        $currentVersion = ExtensiveContactTracing::getSchema()->getCurrentVersion()->getVersion();
        $case->extensiveContactTracing = ExtensiveContactTracing::newInstanceWithVersion($currentVersion, $extensiveContactTracingCallable);

        $case->save();

        $response = $this->be($user)->get(sprintf('/api/copy/%s/diagnostics', $case->uuid));

        $response->assertStatus(200);
        $response->assertSeeTextInOrder($expectedTexts);

        if ($unexpectedTexts) {
            $response->assertDontSeeText($unexpectedTexts);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function extendedBCOV3UpOptionsProvider(): Generator
    {
        yield 'Extensive contact tracing EXTENSIVE' => [
            static function (ExtensiveContactTracingV3Up $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::extensive();
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Uitgebreid',
            ],
            [
                'This is my tracing description field',
            ],
        ];

        yield 'Extensive contact tracing STANDARD' => [
            static function (ExtensiveContactTracingV3Up $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::standard();
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Standaard',
            ],
            [
                'This is my tracing description field',
            ],
        ];

        yield 'Extensive contact tracing OTHER' => [
            static function (ExtensiveContactTracingV3Up $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::other();
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Anders',
                'This is my tracing description field',
            ],
            [],
        ];

        yield 'Extensive contact tracing UNKNOWN' => [
            static function (ExtensiveContactTracingV3Up $contactTracing): void {
                $contactTracing->receivesExtensiveContactTracing = BCOType::unknown();
                $contactTracing->otherDescription = 'This is my tracing description field';
            },
            [
                'Type BCO',
                'Onbekend',
            ],
            [
                'This is my tracing description field',
            ],
        ];
    }
}
