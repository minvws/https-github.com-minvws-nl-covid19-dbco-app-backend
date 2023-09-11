<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use Tests\Feature\FeatureTestCase;

use function config;

class VerifyCsrfTokenTest extends FeatureTestCase
{
    public function testDisabledCsrfVerification(): void
    {
        config()->set('security.disable_csrf_verifications', true);

        $user = $this->createUser();
        $response = $this->be($user)->postJson('api/cases', [
            'index' => [
                'firstname' => $this->faker->firstName(),
                'lastname' => $this->faker->lastName(),
                'dateOfBirth' => $this->faker->date('Y-m-d'),
            ],
            'contact' => [
                'phone' => $this->faker->phoneNumber,
            ],
        ]);

        $response->assertStatus(201);
    }
}
