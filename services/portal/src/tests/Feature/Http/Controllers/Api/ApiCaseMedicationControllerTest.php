<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Medication;
use App\Models\CovidCase\Medicine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-medication')]
class ApiCaseMedicationControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/medication');
        $response->assertStatus(200);
    }

    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        /** @var Medication $medication */
        $medication = Medication::getSchema()->getCurrentVersion()->newInstance();

        /** @var Medicine $medicine */
        $medicine = Medicine::getSchema()->getCurrentVersion()->newInstance();
        $medicine->name = 'foo';

        $medication->hasMedication = YesNoUnknown::yes();
        $medication->isImmunoCompromised = YesNoUnknown::yes();
        $medication->immunoCompromisedRemarks = 'foo';
        $medication->hasGivenPermission = YesNoUnknown::yes();
        $medication->practitioner = 'foo';
        $medication->practitionerPhone = '0610000000';
        $medication->hospitalName = 'foo';
        $medication->medicines = [$medicine];

        $response = $this->be($user)
            ->putJson(
                '/api/cases/' . $case->uuid . '/fragments/medication',
                (array) $medication->getData(),
            );
        $response->assertStatus(200);
    }
}
