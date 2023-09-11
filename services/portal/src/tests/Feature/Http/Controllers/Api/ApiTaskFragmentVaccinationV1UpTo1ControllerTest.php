<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Shared\VaccineInjection;
use App\Models\Versions\Task\TaskV1;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentVaccinationV1UpTo1ControllerTest extends FeatureTestCase
{
    public function testGetEmpty(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'created_at' => new DateTimeImmutable('-2 days'),
            'schema_version' => 1,
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);
        $this->assertArrayHasKey('vaccination', $response->json('data'));
    }

    public function testGet(): void
    {
        $user = $this->createUser();

        /** @var TaskV1 $task */
        $task = $this->createTaskForUser($user, [
            'created_at' => new DateTimeImmutable('now'),
            'schema_version' => 1,
        ]);

        $task->vaccination->isVaccinated = YesNoUnknown::yes();
        $task->vaccination->hasCompletedVaccinationSeries = true;
        $task->vaccination->vaccineInjections = [
            VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjection $vaccineInjection): void {
                $vaccineInjection->vaccineType = Vaccine::other();
                $vaccineInjection->otherVaccineType = 'Magnetic';
                $vaccineInjection->injectionDate = new DateTimeImmutable('2021-08-01');
                $vaccineInjection->isInjectionDateEstimated = false;
            }),
            VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjection $vaccineInjection): void {
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
                'schemaVersion' => 1,
                'isVaccinated' => $task->vaccination->isVaccinated->value,
                'vaccineInjections' => [
                    [
                        'schemaVersion' => 1,
                        'injectionDate' => $vaccineInjectionFirst->injectionDate->format('Y-m-d'),
                        'isInjectionDateEstimated' => $vaccineInjectionFirst->isInjectionDateEstimated,
                        'vaccineType' => $vaccineInjectionFirst->vaccineType->value,
                        'otherVaccineType' => $vaccineInjectionFirst->otherVaccineType,
                    ],
                    [
                        'schemaVersion' => 1,
                        'injectionDate' => $vaccineInjectionSecond->injectionDate->format('Y-m-d'),
                        'isInjectionDateEstimated' => $vaccineInjectionSecond->isInjectionDateEstimated,
                        'vaccineType' => $vaccineInjectionSecond->vaccineType,
                        'otherVaccineType' => $vaccineInjectionSecond->otherVaccineType,
                    ],
                ],
                'hasCompletedVaccinationSeries' => true,
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
            ['schema_version' => 1],
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
            ['schema_version' => 1],
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
                        'hasCompletedVaccinationSeries' => false,
                    ],
                ],
            );

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
                        'schemaVersion' => 1,
                    ],
                ],
                'schemaVersion' => 1,
                'hasCompletedVaccinationSeries' => false,
            ],
            $response->json('data')['vaccination'],
        );
    }
}
