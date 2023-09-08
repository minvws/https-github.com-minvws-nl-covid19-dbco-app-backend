<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Http\Response;

use function config;

class ConsentControllerTest extends ControllerTestCase
{
    public function testConsentRedirect(): void
    {
        $user = $this->createUser(['consented_at' => null]);

        $response = $this->be($user)->get('/');
        $response->assertRedirect('/consent');
    }

    public function testConsentFlow(): void
    {
        $now = $this->faker->dateTime();
        $userName = 'Consented User';

        CarbonImmutable::setTestNow($now);
        $user = $this->createUser([
            'name' => $userName,
        ]);

        $response = $this->be($user)->get('/consent');
        $response->assertViewIs('consent');


        $response = $this->be($user)->post('/consent', ['consent' => true]);
        $response->assertRedirect('/');

        $this->assertAuditObjectForUser($user);
        $this->assertDatabaseHas('bcouser', [
            'uuid' => $user->uuid,
            'name' => $userName,
            'consented_at' => $now,
        ]);
    }

    public function testConsentNoRoles(): void
    {
        $user = $this->createUser([], null);

        $response = $this->be($user)->get('/consent');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testResetConsent(): void
    {
        /** @var EloquentOrganisation $organisation */
        $organisation = EloquentOrganisation::query()
            ->where('uuid', '=', DummySeeder::DEMO_ORGANISATION_UUID)
            ->firstOrFail();
        $user = $this->createUserForOrganisation($organisation, [
            'consented_at' => $this->faker->dateTime(),
        ]);

        $response = $this->be($user)->post('/consent/reset');
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('bcouser', [
            'uuid' => $user->uuid,
            'consented_at' => null,
        ]);
    }

    public function testResetConsentNotResettingOtherOrganisations(): void
    {
        $consentedAt = $this->faker->dateTime();

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [
            'consented_at' => $consentedAt,
        ]);

        $response = $this->be($user)->post('/consent/reset');
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('bcouser', [
            'uuid' => $user->uuid,
            'consented_at' => $consentedAt,
        ]);
    }

    public function testResetConsentAbortsOnConfig(): void
    {
        config()->set('auth.allow_demo_login', false);
        $user = $this->createUser();

        $response = $this->be($user)->post('/consent/reset');
        $response->assertStatus(403);
    }

    public function testShowPrivacyStatement(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->get('/consent/privacy');
        $response->assertStatus(200);

        $this->assertAuditObjectForUser($user);
    }
}
