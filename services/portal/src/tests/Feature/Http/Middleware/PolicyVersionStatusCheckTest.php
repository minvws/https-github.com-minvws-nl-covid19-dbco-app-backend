<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\PolicyVersionStatusCheck;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use MinVWS\Audit\Services\AuditService;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Http\Middleware\Stubs\StubPolicyVersionApiResourceController;

use function sprintf;

#[Group('policy')]
final class PolicyVersionStatusCheckTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var AuditService $auditService */
        $auditService = $this->app->make(AuditService::class);
        $auditService->setEventExpected(false);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router
            ->middleware([SubstituteBindings::class, PolicyVersionStatusCheck::class])
            ->scopeBindings()
            ->prefix('test')
            ->group(static function (Router $router): void {
                $router->apiResource('policy-version.calendar-item', StubPolicyVersionApiResourceController::class);

                $router->post('/without-policy-version', static function (): array {
                    return ['action' => 'without-policy-version'];
                });

                $router->post('/with-policy-version-as-string/{policy_version}', static function (string $policyVersion): array {
                    return [
                        'action' => 'without-policy-version',
                        'policyVersionUuid' => $policyVersion,
                    ];
                });
            });
    }

    public function testEndpointWithoutPolicyVersionParameter(): void
    {
        $this
            ->postJson('/test/without-policy-version')
            ->assertOk()
            ->assertJson(['action' => 'without-policy-version']);
    }

    public function testEndpointWithPolicyVersionParameterAsString(): void
    {
        $uuid = $this->faker->uuid();

        $this
            ->postJson(sprintf('/test/with-policy-version-as-string/%s', $uuid))
            ->assertOk()
            ->assertJson([
                'action' => 'without-policy-version',
                'policyVersionUuid' => $uuid,
            ]);
    }

    public function testIndexWithDraftStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $this
            ->getJson(sprintf('/test/policy-version/%s/calendar-item', $policyVersion->uuid))
            ->assertOk()
            ->assertJson(['action' => 'index']);
    }

    public function testIndexWithActiveStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::active()]);

        $this
            ->getJson(sprintf('/test/policy-version/%s/calendar-item', $policyVersion->uuid))
            ->assertOk()
            ->assertJson(['action' => 'index']);
    }

    public function testShowWithDraftStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJson(['action' => 'show']);
    }

    public function testShowWithActiveStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::active()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->getJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJson(['action' => 'show']);
    }

    public function testDestroyWithDraftStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->deleteJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJson(['action' => 'destroy']);
    }

    public function testDestroyWithActiveStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::active()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->deleteJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['policyVersion' => ['Changes are not allowed unless status is on draft.']]);
    }

    public function testStoreWithDraftStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);

        $this
            ->postJson(sprintf('/test/policy-version/%s/calendar-item', $policyVersion->uuid))
            ->assertOk()
            ->assertJson(['action' => 'store']);
    }

    public function testStoreWithActiveStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::active()]);

        $this
            ->postJson(sprintf('/test/policy-version/%s/calendar-item', $policyVersion->uuid), [])
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['policyVersion' => ['Changes are not allowed unless status is on draft.']]);
    }

    public function testUpdateWithDraftStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::draft()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertOk()
            ->assertJson(['action' => 'update']);
    }

    public function testUpdateWithActiveStatus(): void
    {
        $policyVersion = PolicyVersion::factory()->create(['status' => PolicyVersionStatus::active()]);
        $calendarItem = CalendarItem::factory()->recycle($policyVersion)->create();

        $this
            ->putJson(sprintf('/test/policy-version/%s/calendar-item/%s', $policyVersion->uuid, $calendarItem->uuid))
            ->assertStatus(422)
            ->assertJsonIsObject()
            ->assertJsonValidationErrors(['policyVersion' => ['Changes are not allowed unless status is on draft.']]);
    }
}
