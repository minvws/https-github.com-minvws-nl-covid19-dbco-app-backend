<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\AlternateResidency;
use App\Models\Shared\Address;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
final class ApiCaseAlternateResidencyControllerTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/alternate-residency', $case->uuid));
        $response->assertStatus(200);
    }

    public function testWithPayload(): void
    {
        /** @var Address $address */
        $address = Address::getSchema()->getCurrentVersion()->newInstance();
        $address->town = 'Ghost town';
        $address->postalCode = '0000XX';
        $address->houseNumber = '0';
        $address->houseNumberSuffix = 'A';
        $address->street = 'Paragon street';

        /** @var AlternateResidency $alternateResidency */
        $alternateResidency = AlternateResidency::getSchema()->getCurrentVersion()->newInstance();
        $alternateResidency->remark = 'boycott';
        $alternateResidency->hasAlternateResidency = YesNoUnknown::yes();
        $alternateResidency->address = $address;

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $case->alternateResidency()->save($alternateResidency);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/alternate-residency', $case->uuid));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($alternateResidency->remark, $data['remark']);
        $this->assertEquals($alternateResidency->hasAlternateResidency, $data['hasAlternateResidency']);

        $this->assertEquals($address->postalCode, $data['address']['postalCode']);
        $this->assertEquals($address->town, $data['address']['town']);
        $this->assertEquals($address->houseNumber, $data['address']['houseNumber']);
        $this->assertEquals($address->houseNumberSuffix, $data['address']['houseNumberSuffix']);
        $this->assertEquals($address->street, $data['address']['street']);
    }

    public function testPut(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no fields required
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternate-residency');
        $response->assertStatus(200);

        // store value
        $response = $this->be($user)
            ->putJson(
                '/api/cases/' . $case->uuid . '/fragments/alternate-residency',
                [
                    'hasAlternateResidency' => YesNoUnknown::no()->value,
                ],
            );
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no()->value, $data['data']['hasAlternateResidency']);

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternate-residency');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::no()->value, $data['data']['hasAlternateResidency']);

        // store changed value
        $response = $this->be($user)
            ->putJson(
                '/api/cases/' . $case->uuid . '/fragments/alternate-residency',
                [
                    'hasAlternateResidency' => YesNoUnknown::yes(),
                ],
            );
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes()->value, $data['data']['hasAlternateResidency']);

        // check if the changed value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternate-residency');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes()->value, $data['data']['hasAlternateResidency']);
    }
}
