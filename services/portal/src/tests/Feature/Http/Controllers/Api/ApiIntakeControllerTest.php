<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;
use function json_encode;

#[Group('intake')]
class ApiIntakeControllerTest extends FeatureTestCase
{
    public function testIntakeList(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $labels = $this->getLabels();
        $this->createIntakesForTest($organisation, $labels);

        $response = $this->be($user)->getJson('/api/intakes');
        $this->assertStatus($response, 200);
        $this->assertEquals(3, count($response->json('data')));
        $this->assertEquals('1', $response->json('data.0.identifier'));
        $this->assertEquals('2', $response->json('data.1.identifier'));
        $this->assertEquals('3', $response->json('data.2.identifier'));
    }

    public function testIntakeListWithTotal(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $labels = $this->getLabels();
        $this->createIntakesForTest($organisation, $labels);

        $response = $this->be($user)->getJson('/api/intakes?includeTotal=1');
        $this->assertStatus($response, 200);
        $this->assertEquals(3, $response->json('total'));
    }

    public function testIntakeListSort(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $labels = $this->getLabels();
        $this->createIntakesForTest($organisation, $labels);

        $response = $this->be($user)->getJson('/api/intakes?sort=dateOfSymptomOnset&order=desc');
        $this->assertStatus($response, 200);
        $this->assertEquals('3', $response->json('data.0.identifier'));
        $this->assertEquals('1', $response->json('data.1.identifier'));
        $this->assertEquals('2', $response->json('data.2.identifier'));
    }

    public function testIntakeListFilterSingle(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $labels = $this->getLabels();
        $this->createIntakesForTest($organisation, $labels);

        $labelFilter = [
            "caseLabels" => $labels[0]->uuid,
        ];
        $response = $this->be($user)->getJson('/api/intakes?filter=' . json_encode($labelFilter));
        $this->assertStatus($response, 200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('1', $response->json('data.0.identifier'));
    }

    public function testIntakeListFilterMultiple(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $labels = $this->getLabels();

        $this->createIntakesForTest($organisation, $labels);

        $labelFilter = [
            "caseLabels" => [
                $labels[0]->uuid,
                $labels[1]->uuid,
            ],
        ];
        $response = $this->be($user)->getJson('/api/intakes?filter=' . json_encode($labelFilter));
        $this->assertStatus($response, 200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('1', $response->json('data.0.identifier'));
        $this->assertEquals('2', $response->json('data.1.identifier'));
    }

    private function createIntakesForTest(EloquentOrganisation $organisation, array $labels = []): void
    {
        $now = CarbonImmutable::now();

        $this->createIntakeForOrganisationWithLabels(
            $organisation,
            [
                'identifier' => '1',
                'dateOfSymptomOnset' => $now->clone()->subDays(7)->format('Y-m-d'),
                'received_at' => $now->format('Y-m-d'),
            ],
            new Collection([$labels[0]]),
        );
        $this->createIntakeForOrganisationWithLabels(
            $organisation,
            [
                'identifier' => '2',
                'dateOfSymptomOnset' => $now->clone()->subDays(9)->format('Y-m-d'),
                'received_at' => $now->subDays(1)->format('Y-m-d'),
            ],
            new Collection([$labels[1]]),
        );
        $this->createIntakeForOrganisation(
            $organisation,
            [
                'identifier' => '3',
                'dateOfSymptomOnset' => $now->clone()->subDays(5)->format('Y-m-d'),
                'received_at' => $now->subDays(2)->format('Y-m-d'),
            ],
        );
    }

    /**
     * @return array
     */
    private function getLabels(): array
    {
        $labels = [];
        $labels[] = $this->createCaseLabel();
        $labels[] = $this->createCaseLabel();

        return $labels;
    }
}
