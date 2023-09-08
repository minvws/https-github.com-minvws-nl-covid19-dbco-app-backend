<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\Config;
use App\Jobs\CaseMetricsRefreshJob;
use App\Models\Eloquent\CaseMetrics;
use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Generator;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\Feature\FeatureTestCase;

use function array_column;
use function array_reverse;
use function route;
use function sprintf;

class ApiCaseMetricsControllerTest extends FeatureTestCase
{
    private EloquentOrganisation $organisation;
    private int $numDaysInPast;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();
        $this->numDaysInPast = Config::integer('casemetrics.created_archived_days_in_past');
    }

    #[DataProvider('provideRolesAndIsAccessible')]
    public function testGetCreatedArchivedAccessibility(string $role, bool $isAccessible): void
    {
        $response = $this->callGetCreatedArchivedEndpoint($role);
        $isAccessible ? $response->assertOk() : $response->assertForbidden();
    }

    public static function provideRolesAndIsAccessible(): Generator
    {
        yield '`planner` role' => ['planner', true];
        yield '`planner_nationwide` role' => ['planner_nationwide', false];
        yield '`user` role' => ['user', false];
        yield '`conversation_coach` role' => ['conversation_coach', false];
        yield '`medical_supervisor` role' => ['medical_supervisor', false];
    }

    public function testGetCreatedArchivedReturnsDatesInDescendingOrder(): void
    {
        CarbonImmutable::setTestNow($this->faker->dateTimeBetween('-1 year'));
        $now = CarbonImmutable::now();
        $this->refreshCaseMetrics();

        $metrics = $this->getCreatedArchivedJson();

        // Expected count is configured number of days in past plus today
        $this->assertCount($this->numDaysInPast + 1, $metrics);
        foreach (array_column($metrics, 'date') as $date) {
            $this->assertEquals($now->setTime(0, 0)->format('c'), $date);
            $now = $now->subDay();
        }
    }

    public function testGetCreatedArchivedReturnsEmptyResponseWhenNoOrganisation(): void
    {
        // GIVEN a user with the planner role and existing case metrics
        $this->refreshCaseMetrics();

        // WHEN that user does not have any organisation associated to them
        $response = $this->callGetCreatedArchivedEndpoint('planner', false);

        // THEN assert the response is completely empty
        $this->assertEquals(
            [
                'refreshedAt' => null,
                'eTag' => null,
                'data' => [],
            ],
            $response->json(),
        );
    }

    public function testGetCreatedArchivedReturnsEmptyResponseWhenNoCaseMetrics(): void
    {
        $response = $this->callGetCreatedArchivedEndpoint('planner');

        $this->assertEquals(
            [
                'refreshedAt' => null,
                'eTag' => null,
                'data' => [],
            ],
            $response->json(),
        );
    }

    public function testGetCreatedArchivedReturnsFirstRefreshedAt(): void
    {
        $formattedTimestamp = $this->faker->dateTimeBetween('-1 year')->format('c');
        CarbonImmutable::setTestNow($formattedTimestamp);
        $this->refreshCaseMetrics();

        $response = $this->getCreatedArchivedJson(false);

        $this->assertEquals($formattedTimestamp, $response['refreshedAt']);
    }

    public function testGetCreatedArchivedWithETagReturnsDataIfResourceChanged(): void
    {
        $this->refreshCaseMetrics();
        $response = $this->callGetCreatedArchivedEndpoint('planner', true, ['If-None-Match' => $this->faker->md5()]);

        $response->assertOk();
        $responseJson = $response->json();
        $this->assertNotEmpty($responseJson['data']);
    }

    public function testGetCreatedArchivedWithETagReturns304IfResourceUnchanged(): void
    {
        // GIVEN a valid ETag
        $this->refreshCaseMetrics();
        $response = $this->callGetCreatedArchivedEndpoint('planner');
        $response->assertOk();

        $responseJson = $response->json();
        $this->assertArrayHasKey('eTag', $responseJson);

        // WHEN that ETag is sent with the list request
        $response = $this->callGetCreatedArchivedEndpoint('planner', true, ['If-None-Match' => $responseJson['eTag']]);

        // THEN expect an empty 304 response, since the resource has not changed
        $this->assertEquals(304, $response->status());
        $this->assertEmpty($response->content());
    }

    public function testRefreshCreatedArchivedQueuesJob(): void
    {
        Queue::fake();
        $user = $this->createUserForOrganisation($this->organisation, [], 'planner');
        $response = $this->be($user)->post(route('api-cases-metrics-created-archived-refresh'));

        $this->assertStatus($response, SymfonyResponse::HTTP_NO_CONTENT);
        Queue::assertPushed(function (CaseMetricsRefreshJob $job) {
            $this->assertEquals($this->organisation->uuid, $job->organisationUuid);
            $this->assertEquals('00:00', $job->periodEnd->format('H:i'));

            return true;
        });
    }

    public function testRefreshCreatedArchivedSkipsJobWithoutOrganisation(): void
    {
        Queue::fake();
        $user = $this->createUserWithoutOrganisation([], 'planner');
        $response = $this->be($user)->post(route('api-cases-metrics-created-archived-refresh'));

        $this->assertStatus($response, SymfonyResponse::HTTP_NO_CONTENT);
        Queue::assertNotPushed(CaseMetricsRefreshJob::class);
    }

    private function refreshCaseMetrics(): void
    {
        $now = CarbonImmutable::now();
        $period = CarbonPeriod::between($now->modify(sprintf('-%d days', $this->numDaysInPast)), $now);
        foreach (array_reverse($period->toArray()) as $day) {
            CaseMetrics::factory()
                ->for($this->organisation, 'organisation')
                ->state([
                    'date' => $day->format('Y-m-d'),
                    'refreshed_at' => $now,
                ])
                ->create();
        }
    }

    private function getCreatedArchivedJson(bool $onlyMetrics = true): array
    {
        $response = $this->callGetCreatedArchivedEndpoint('planner');
        $response->assertOk();

        $responseJson = $response->json();
        $this->assertIsArray($responseJson);
        $this->assertArrayHasKey('refreshedAt', $responseJson);
        $this->assertArrayHasKey('eTag', $responseJson);
        $this->assertArrayHasKey('data', $responseJson);

        return $onlyMetrics ? $responseJson['data'] : $responseJson;
    }

    private function callGetCreatedArchivedEndpoint(string $role, bool $withOrganisation = true, array $headers = []): TestResponse
    {
        $user = $withOrganisation
            ? $this->createUserForOrganisation($this->organisation, [], $role)
            : $this->createUserWithoutOrganisation([], $role);

        return $this->be($user)->getJson(route('api-cases-metrics-created-archived'), $headers);
    }
}
