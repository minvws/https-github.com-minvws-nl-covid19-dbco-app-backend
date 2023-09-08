<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV2Up;
use App\Models\Versions\CovidCase\Vaccination\VaccinationCommon;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo1;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV2Up;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\VaccinationGroup;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-vaccination')]
class ApiCaseVaccinationControllerTest extends FeatureTestCase
{
    public function testGetEmpty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/vaccination');
        $response->assertStatus(200);
    }

    public function testNotFoundForNonExistingCase(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/vaccination');
        $response->assertStatus(404);
    }

    public function testGetVaccinationV1UpToV1(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 1]);

        $case->vaccination->isVaccinated = YesNoUnknown::yes();
        $case->vaccination->hasReceivedInvite = YesNoUnknown::yes();
        $case->vaccination->hasCompletedVaccinationSeries = true;
        $case->vaccination->vaccineInjections = $this->getInjections(1);

        $case->save();

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/vaccination');
        $response->assertStatus(200);

        $this->assertEquals(
            [
                'hasReceivedInvite' => $case->vaccination->hasReceivedInvite->value,
                'groups' => null,
                'otherGroup' => null,
                'isVaccinated' => $case->vaccination->isVaccinated->value,
                'vaccineInjections' => [
                    [
                        'schemaVersion' => 1,
                        'injectionDate' => $case->vaccination->vaccineInjections[0]->injectionDate->format('Y-m-d'),
                        'vaccineType' => $case->vaccination->vaccineInjections[0]->vaccineType->value,
                        'otherVaccineType' => null,
                        'isInjectionDateEstimated' => true,
                    ],
                ],
                'hasCompletedVaccinationSeries' => true,
                'schemaVersion' => 1,
            ],
            $response->json('data'),
        );
    }

    public function testGetVaccinationV2Up(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 2]);

        $case->vaccination->isVaccinated = YesNoUnknown::yes();
        $case->vaccination->vaccineInjections = $this->getInjections(VaccineInjection::getSchema()->getCurrentVersion()->getVersion());

        $case->save();

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/vaccination');
        $response->assertStatus(200);

        $this->assertEquals(
            [
                'isVaccinated' => $case->vaccination->isVaccinated->value,
                'vaccineInjections' => [
                    [
                        'schemaVersion' => 1,
                        'injectionDate' => $case->vaccination->vaccineInjections[0]->injectionDate->format('Y-m-d'),
                        'vaccineType' => $case->vaccination->vaccineInjections[0]->vaccineType->value,
                        'otherVaccineType' => null,
                        'isInjectionDateEstimated' => true,
                    ],
                ],
                'schemaVersion' => 2,
            ],
            $response->json('data'),
        );
    }

    public function testPostVaccinationV1UpTo1(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 1]);

        // check no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/vaccination');
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/fragments/vaccination',
            [
                'hasReceivedInvite' => YesNoUnknown::yes()->value,
                'isVaccinated' => YesNoUnknown::yes()->value,
                'otherGroup' => 'Older than 100 years old.',
                'groups' => [
                    VaccinationGroup::ageBelow60(),
                ],
                'vaccineInjections' => [
                    [
                        'injectionDate' => '2021-01-01',
                        'vaccineType' => Vaccine::other()->value,
                        'otherVaccineType' => 'magnetic',
                    ],
                ],
                'hasCompletedVaccinationSeries' => true,
            ],
        );

        $response->assertStatus(200);

        $case = $case->refresh();

        $this->assertEquals(1, $case->getSchemaVersion()->getVersion());
        $this->assertEquals(1, $case->vaccination->getSchemaVersion()->getVersion());

        /** @var VaccinationV1UpTo1|VaccinationCommon $vaccination */
        $vaccination = $case->vaccination;
        $this->assertEquals(YesNoUnknown::yes(), $vaccination->hasReceivedInvite);
        $this->assertEquals(YesNoUnknown::yes(), $vaccination->isVaccinated);
        $this->assertCount(1, $vaccination->vaccineInjections);
    }

    public function testPostVaccinationV2Up(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // check no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/vaccination');
        $response->assertStatus(200);

        // check storage
        $response = $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/fragments/vaccination',
            [
                'isVaccinated' => YesNoUnknown::yes()->value,
                'vaccineInjections' => [
                    [
                        'injectionDate' => '2021-01-01',
                        'vaccineType' => Vaccine::other()->value,
                        'otherVaccineType' => 'magnetic',
                    ],
                ],
            ],
        );

        $response->assertStatus(200);

        /** @var CovidCaseV2Up $case */
        $case = EloquentCase::find($case->uuid);

        /** @var VaccinationV2Up $vaccination */
        $vaccination = $case->vaccination;
        $this->assertEquals(YesNoUnknown::yes(), $vaccination->isVaccinated);
        $this->assertCount(1, $vaccination->vaccineInjections);
    }

    private function getInjections(int $schemaVersion): array
    {
        return [VaccineInjection::newInstanceWithVersion($schemaVersion, static function (VaccineInjection $vaccineInjection): void {
            $vaccineInjection->vaccineType = Vaccine::pfizer();
            $vaccineInjection->injectionDate = new DateTimeImmutable('2021-09-08');
            $vaccineInjection->isInjectionDateEstimated = true;
        })];
    }
}
