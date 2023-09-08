<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Vaccination;
use App\Models\Shared\VaccineInjection;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentVaccinationV2UpControllerTest extends FeatureTestCase
{
    public function testGetEmpty(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser(
            $user,
            ['created_at' => new DateTimeImmutable('-2 days')],
            ['schema_version' => 4],
        );

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);
        $this->assertArrayHasKey('vaccination', $response->json('data'));
    }

    public function testGet(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser(
            $user,
            ['created_at' => new DateTimeImmutable('now')],
            ['schema_version' => 4],
        );

        $injectionVersion = VaccineInjection::getSchema()->getCurrentVersion()->getVersion();

        $task->vaccination->isVaccinated = YesNoUnknown::yes();
        $task->vaccination->vaccineInjections = [
            VaccineInjection::newInstanceWithVersion($injectionVersion, static function (VaccineInjection $vaccineInjection): void {
                $vaccineInjection->vaccineType = Vaccine::other();
                $vaccineInjection->otherVaccineType = 'Magnetic';
                $vaccineInjection->injectionDate = new DateTimeImmutable('2021-08-01');
                $vaccineInjection->isInjectionDateEstimated = false;
            }),
            VaccineInjection::newInstanceWithVersion($injectionVersion, static function (VaccineInjection $vaccineInjection): void {
                $vaccineInjection->vaccineType = Vaccine::moderna();
                $vaccineInjection->injectionDate = new DateTimeImmutable('2021-09-01');
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
        ];

        $task->save();

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);

        $vaccineInjectionFirst = $task->vaccination->vaccineInjections[0];
        $vaccineInjectionSecond = $task->vaccination->vaccineInjections[1];

        $this->assertEquals(
            [
                'schemaVersion' => Vaccination::getSchema()->getCurrentVersion()->getVersion(),
                'isVaccinated' => $task->vaccination->isVaccinated->value,
                'vaccineInjections' => [
                    [
                        'schemaVersion' => $injectionVersion,
                        'injectionDate' => $vaccineInjectionFirst->injectionDate->format('Y-m-d'),
                        'isInjectionDateEstimated' => $vaccineInjectionFirst->isInjectionDateEstimated,
                        'vaccineType' => $vaccineInjectionFirst->vaccineType->value,
                        'otherVaccineType' => $vaccineInjectionFirst->otherVaccineType,
                    ],
                    [
                        'schemaVersion' => $injectionVersion,
                        'injectionDate' => $vaccineInjectionSecond->injectionDate->format('Y-m-d'),
                        'isInjectionDateEstimated' => $vaccineInjectionSecond->isInjectionDateEstimated,
                        'vaccineType' => $vaccineInjectionSecond->vaccineType,
                        'otherVaccineType' => $vaccineInjectionSecond->otherVaccineType,
                    ],
                ],
                'vaccinationCount' => null,
            ],
            $response->json('data')['vaccination'],
        );
    }

    public function testPutWithoutRequiredFields(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser(
            $user,
            ['created_at' => new DateTimeImmutable('-2 days')],
            ['schema_version' => 4],
        );

        $response = $this->be($user)->putJson(
            sprintf('/api/tasks/%s/fragments', $task->uuid),
            ['vaccination' => null],
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('vaccination', $response->json('data'));
    }

    public function testPutWithFields(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser(
            $user,
            ['created_at' => new DateTimeImmutable('-2 days')],
            ['schema_version' => 4],
        );

        $response = $this->be($user)
            ->putJson(
                sprintf('/api/tasks/%s/fragments', $task->uuid),
                [
                    'vaccination' => [
                        'isVaccinated' => YesNoUnknown::yes()->value,
                        'vaccineInjections' => [
                            [
                                'injectionDate' => '2021-09-01',
                                'isInjectionDateEstimated' => true,
                                'vaccineType' => Vaccine::moderna()->value,
                                'otherVaccineType' => null,
                            ],
                        ],
                    ],
                ],
            );
        $injectionVersion = VaccineInjection::getSchema()->getCurrentVersion()->getVersion();
        $vaccinationVersion = Vaccination::getSchema()->getCurrentVersion()->getVersion();


        $response->assertStatus(200);
        $this->assertEquals(
            [
                'isVaccinated' => YesNoUnknown::yes()->value,
                'vaccineInjections' => [
                    [
                        'injectionDate' => '2021-09-01',
                        'isInjectionDateEstimated' => true,
                        'vaccineType' => Vaccine::moderna()->value,
                        'otherVaccineType' => null,
                        'schemaVersion' => $injectionVersion,
                    ],
                ],
                'schemaVersion' => $vaccinationVersion,
                'vaccinationCount' => null,
            ],
            $response->json('data')['vaccination'],
        );
    }
}
