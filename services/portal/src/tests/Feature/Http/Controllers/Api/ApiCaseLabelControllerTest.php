<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class ApiCaseLabelControllerTest extends FeatureTestCase
{
    public function testGetCaseLabels(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'foo'], ['sortorder' => 10]);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'bar'], ['sortorder' => 20]);

        $response = $this->be($user)->getJson('/api/caselabels');

        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'is_selectable' => true,
                'uuid' => $caseLabel2->uuid,
                'label' => 'bar',
            ],
            [
                'is_selectable' => true,
                'uuid' => $caseLabel1->uuid,
                'label' => 'foo',
            ],
        ]);
    }

    public function testGetCaseLabelsForOtherOrganisationNotVisible(): void
    {
        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation1);

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation1, ['label' => 'foo'], ['sortorder' => 10]);
        $this->createCaseLabelForOrganisation($organisation2, ['label' => 'bar'], ['sortorder' => 10]);

        $response = $this->be($user)->getJson('/api/caselabels');

        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'is_selectable' => true,
                'uuid' => $caseLabel1->uuid,
                'label' => 'foo',
            ],
        ]);
    }

    #[DataProvider('caseLabelAuthorizationDataProvider')]
    public function testCaseLabelAuthorization(string $roles, int $expectedStatusCode): void
    {
        $user = $this->createUser([], $roles);

        $response = $this->be($user)->getJson('/api/caselabels');

        $response->assertStatus($expectedStatusCode);
    }

    public static function caseLabelAuthorizationDataProvider(): array
    {
        return [
            'user' => ['user', 200],
            'planner' => ['planner', 200],
            'user,planner' => ['user,planner', 200],
            'user,compliance' => ['user,compliance', 200],
            'compliance' => ['compliance', 403],
        ];
    }

    public function testGetCaseLabelsNotSelectable(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'foo'], ['sortorder' => 10]);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'bar', 'is_selectable' => false]);

        $response = $this->be($user)->getJson('/api/caselabels');

        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'is_selectable' => true,
                'uuid' => $caseLabel1->uuid,
                'label' => 'foo',
            ],
            [
                'is_selectable' => false,
                'uuid' => $caseLabel2->uuid,
                'label' => 'bar',
            ],
        ]);
    }
}
