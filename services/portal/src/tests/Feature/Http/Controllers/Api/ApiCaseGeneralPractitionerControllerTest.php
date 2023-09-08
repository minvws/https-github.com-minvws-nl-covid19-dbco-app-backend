<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
final class ApiCaseGeneralPractitionerControllerTest extends FeatureTestCase
{
    public function testEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/general-practitioner', $case->uuid));
        $response->assertStatus(200);
    }

    public function testWithValidPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)
            ->putJson(
                '/api/cases/' . $case->uuid . '/fragments/general-practitioner',
                [
                    'name' => 'Dr Strange',
                    'practiceName' => 'Avengers',
                    'address' => [
                        'postalCode' => '1234AA',
                        'houseNumber' => '23',
                        'houseNumberSuffix' => 'pre',
                        'street' => 'Magic Avenue',
                        'town' => 'Ghost Town',
                    ],
                    'hasInfectionNotificationConsent' => true,
                ],
            );

        $response->assertStatus(200);
    }

    public function testWithNonNlPostalCode(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $payload = [
            'name' => 'Dr Strange',
            'practiceName' => 'Avengers',
            'address' => [
                'postalCode' => '9420',
                'houseNumber' => '11',
                'houseNumberSuffix' => 'pre',
                'street' => 'Magic Avenue',
                'town' => 'Ghost Town',
            ],
            'hasInfectionNotificationConsent' => true,
        ];

        $response = $this->be($user)
            ->putJson('/api/cases/' . $case->uuid . '/fragments/general-practitioner', $payload);

        $expectedResult = [
            'address.postalCode' => [
                'PostalCode' => ['NL'],
            ],
        ];
        $this->assertEquals($expectedResult, $response->json('validationResult')['warning']['failed']);
    }
}
