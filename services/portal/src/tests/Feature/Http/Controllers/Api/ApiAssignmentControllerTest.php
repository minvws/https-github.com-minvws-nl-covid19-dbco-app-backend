<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Services\Assignment\AssignmentTokenService;
use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Config\Repository as Config;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('assignment')]
final class ApiAssignmentControllerTest extends FeatureTestCase
{
    private string $assignmentHeader;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Config $config */
        $config = $this->app->make(Config::class);
        $config->set('assignment.jwt.secret', 'MY_JWT_SECRET');

        $this->assignmentHeader = $config->get('assignment.token_fetcher.request_header.header_name');
    }

    public function testAssignSingleCase(): void
    {
        $user = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );
        $case = $this->createCaseForOrganisation($user->getOrganisation());

        $this->be($user);

        $this
            ->getJson("/editcase/$case->uuid")
            ->assertStatus(403);

        /** @var AssignmentTokenService $assignmentTokenService */
        $assignmentTokenService = $this->app->make(AssignmentTokenService::class);
        $token = $assignmentTokenService->createTokenForCases([$case->uuid], $user);

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}", headers: [$this->assignmentHeader => $token])
            ->assertStatus(200);

        $this
            ->get("/editcase/$case->uuid")
            ->assertStatus(200)
            ->assertViewIs('editcase');
    }

    public function testAssignSingleCasesUnauthenticated(): void
    {
        $case = $this->createCase();

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}")
            ->assertStatus(401);
    }

    public function testAssignSingleCaseWithCaseNotAuthorized(): void
    {
        $user = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );

        $case1 = $this->createCaseForOrganisation($user->getOrganisation());
        $case2 = $this->createCaseForOrganisation($user->getOrganisation());

        /** @var AssignmentTokenService $assignmentTokenService */
        $assignmentTokenService = $this->app->make(AssignmentTokenService::class);
        $token = $assignmentTokenService->createTokenForCases([$case1->uuid], $user);

        $this->be($user);

        $this
            ->get("/editcase/$case2->uuid")
            ->assertStatus(403);

        $this
            ->postJson("/api/assignment/cases/{$case2->uuid}", headers: [$this->assignmentHeader => $token])
            ->assertStatus(403);

        $this
            ->get("/editcase/$case2->uuid")
            ->assertStatus(403);
    }

    public function testAssignSingleCaseWithExpiredToken(): void
    {
        $user = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );

        $case = $this->createCaseForOrganisation($user->getOrganisation());

        $expiredToken = CarbonImmutable::withTestNow(
            CarbonImmutable::now()->subHour(),
            function () use ($user, $case): string {
                /** @var AssignmentTokenService $assignmentTokenService */
                $assignmentTokenService = $this->app->make(AssignmentTokenService::class);

                return $assignmentTokenService->createTokenForCases([$case->uuid], $user);
            },
        );

        $this->be($user);

        $this
            ->get("/editcase/$case->uuid")
            ->assertStatus(403);

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}", headers: [$this->assignmentHeader => $expiredToken])
            ->assertStatus(401);

        $this
            ->get("/editcase/$case->uuid")
            ->assertStatus(403);
    }

    public function testAssignSingleCaseWithInvalidToken(): void
    {
        $user = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );
        $organisation = $user->getOrganisation();
        $case = $this->createCaseForOrganisation($organisation);

        $differentJwtKey = $this->faker->words(asText: true);
        $invalidToken = JWT::encode(['sub' => $this->faker->randomNumber()], $differentJwtKey, 'HS256');

        $this->be($user);

        $this
            ->get("/editcase/$case->uuid")
            ->assertStatus(403);

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}", headers: [$this->assignmentHeader => $invalidToken])
            ->assertStatus(400);

        $this
            ->get("/editcase/$case->uuid")
            ->assertStatus(403);
    }

    public function testAssignSingleCasesWithNotExistingCase(): void
    {
        $user = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );

        $nonExistingCaseUuid = $this->faker->uuid();

        $this->be($user);

        $this
            ->get("/editcase/$nonExistingCaseUuid")
            ->assertStatus(404);

        $this
            ->postJson("/api/assignment/cases/{$nonExistingCaseUuid}")
            ->assertStatus(404);

        $this
            ->get("/editcase/$nonExistingCaseUuid")
            ->assertStatus(404);
    }

    public function testAssignSingleCaseOutsideOfOwnOrg(): void
    {
        $userOne = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );

        $userTwo = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );
        $case = $this->createCaseForOrganisation($userTwo->getOrganisation());

        $this->be($userOne);

        /** @var AssignmentTokenService $assignmentTokenService */
        $assignmentTokenService = $this->app->make(AssignmentTokenService::class);
        $token = $assignmentTokenService->createTokenForCases([$case->uuid], $userOne);

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}", headers: [$this->assignmentHeader => $token])
            ->assertStatus(200);

        $this
            ->getJson("/api/case/$case->uuid")
            ->assertStatus(200);
    }

    public function testAssignSingleCaseOutsideOfOwnOrgHttpRequest(): void
    {
        $userOne = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );

        $userTwo = $this->createUserWithOrganisation(
            ['consented_at' => $this->faker->dateTime()],
            roles: 'callcenter_expert',
        );
        $case = $this->createCaseForOrganisation($userTwo->getOrganisation());

        /** @var AssignmentTokenService $assignmentTokenService */
        $assignmentTokenService = $this->app->make(AssignmentTokenService::class);
        $token = $assignmentTokenService->createTokenForCases([$case->uuid], $userOne);

        $this->be($userOne);

        $this
            ->postJson("/api/assignment/cases/{$case->uuid}", headers: [$this->assignmentHeader => $token])
            ->assertStatus(200);

        $this
            ->getJson("/editcase/$case->uuid")
            ->assertStatus(200);
    }
}
