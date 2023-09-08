<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Chores;

use App\Models\CallToAction\ListOptions;
use App\Services\Chores\CallToActionService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function substr_count;

#[Group('call-to-action')]
class CallToActionHistoryServiceTest extends FeatureTestCase
{
    private CallToActionService $callToActionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->callToActionService = app(CallToActionService::class);
    }

    public function testDownloadCase(): void
    {
        // Log in as user
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with a CallToAction
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        // Assign the logged in user to the Chore associated with the CallToAction
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        // Complete CallToAction with note
        $note = $this->faker->text();
        $this->callToActionService->completeCallToAction($callToAction, $note);

        // Log in as compliance officer
        $complianceOfficer = $this->createUserForOrganisation($organisation, [], 'compliance');
        $this->be($complianceOfficer);

        // Download case as html
        $response = $this->getJson('/api/access-requests/case/' . $case->uuid . '/download/html');
        $content = $response->content();

        // Assert that the downloaded html contains information about the CallToAction history
        self::assertStringContainsString('<h1>Taken en Acties</h1>', $content);
        self::assertStringContainsString($callToAction->subject, $content);
        self::assertStringContainsString($callToAction->description, $content);
        self::assertStringContainsString('Afgerond op', $content);
        self::assertStringContainsString('Notitie geplaatst op', $content);
        self::assertStringContainsString($note, $content);
        self::assertStringContainsString('Opgepakt op', $content);
        self::assertStringNotContainsString('Er zijn meer taken op dit dossier', $content);
        self::assertStringNotContainsString('Er zijn meer gebeurtenissen of notities op deze taak', $content);
    }

    public function testDownloadCaseForExpiredAssignment(): void
    {
        // Log in as user
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with a CallToAction
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        // Assign the logged in user to the Chore associated with the CallToAction

        $expiredAt = CarbonImmutable::parse($this->faker->dateTime(CarbonImmutable::now()->subDay()));
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
            'created_at' => $expiredAt->subDay(),
            'expires_at' => $expiredAt,
        ]);

        // Log in as compliance officer
        $complianceOfficer = $this->createUserForOrganisation($organisation, [], 'compliance');
        $this->be($complianceOfficer);

        // Download case as html
        $response = $this->getJson('/api/access-requests/case/' . $case->uuid . '/download/html');
        $content = $response->content();

        // Assert that the downloaded html contains information about the CallToAction history
        self::assertStringContainsString('<h1>Taken en Acties</h1>', $content);
        self::assertStringContainsString($callToAction->subject, $content);
        self::assertStringContainsString($callToAction->description, $content);
        self::assertStringContainsString('Verlopen op', $content);
        self::assertStringContainsString('Opgepakt op', $content);
        self::assertStringNotContainsString('Er zijn meer taken op dit dossier', $content);
        self::assertStringNotContainsString('Er zijn meer gebeurtenissen of notities op deze taak', $content);
    }

    public function testDownloadNoMoreThanTenChores(): void
    {
        // Log in as user
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with eleven CallToActions
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        for ($i = 1; $i <= 11; $i++) {
            $callToAction = $this->createCallToAction();
            $resource = $this->createResourceForCallToAction($callToAction);
            $this->createChoreForCaseAndOrganisation($case, $organisation, [
                'owner_resource_type' => $resource->type,
                'owner_resource_id' => $resource->id,
            ]);
        }

        // Log in as compliance officer
        $complianceOfficer = $this->createUserForOrganisation($organisation, [], 'compliance');
        $this->be($complianceOfficer);

        // Download case as html
        $response = $this->getJson('/api/access-requests/case/' . $case->uuid . '/download/html');
        $content = $response->content();

        // Assert that the downloaded html contains no more than 10 CallToActions
        self::assertEquals(10, substr_count($content, 'call-to-action-subject'));
        self::assertStringContainsString('Er zijn meer taken op dit dossier', $content);
    }

    public function testDownloadNoMoreThanTenChoreAssignmentsAndCallToActionNotes(): void
    {
        // Log in as user
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with a CallToAction
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        // Create 10 assignments which will be dropped
        for ($i = 1; $i <= 10; $i++) {
            // Assign the logged in user to the Chore associated with the CallToAction
            $this->createAssignment([
                'chore_uuid' => $chore->uuid,
                'user_uuid' => $user->uuid,
            ]);

            // Complete CallToAction with note
            $note = $this->faker->text();
            $this->callToActionService->dropCallToAction($callToAction, $note);
        }

        // Create 5 assignments which will be expired
        for ($i = 1; $i <= 10; $i++) {
            // Assign the logged in user to the Chore associated with the CallToAction
            $expiredAt = CarbonImmutable::parse($this->faker->dateTime(CarbonImmutable::now()->subDay()));
            $this->createAssignment([
                'chore_uuid' => $chore->uuid,
                'user_uuid' => $user->uuid,
                'created_at' => $expiredAt->subDay(),
                'expires_at' => $expiredAt,
            ]);
        }

        // Log in as compliance officer
        $complianceOfficer = $this->createUserForOrganisation($organisation, [], 'compliance');
        $this->be($complianceOfficer);

        // Download case as html
        $response = $this->getJson('/api/access-requests/case/' . $case->uuid . '/download/html');
        $content = $response->content();

        // Assert that the downloaded html contains no more than 30 CallToAction events.
        // 20 assignments, 10 dropped with notes, 10 expires
        self::assertEquals(30, substr_count($content, 'call-to-action-event'));
        self::assertEquals(10, substr_count($content, 'call-to-action-note'));
        self::assertStringContainsString('Er zijn meer gebeurtenissen of notities op deze taak', $content);
    }

    public function testCallToActionListShouldBeEmptyWhenCaseIsDeleted(): void
    {
        // Log in as user
        $user = $this->createUserWithOrganisation();
        $this->be($user);

        $organisation = $user->getOrganisation();

        // Create a case with a CallToAction
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::open()]);
        $callToAction = $this->createCallToAction();
        $resource = $this->createResourceForCallToAction($callToAction);
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => $resource->type,
            'owner_resource_id' => $resource->id,
        ]);

        // Assign the logged in user to the Chore associated with the CallToAction
        $this->createAssignment([
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $case->delete();

        $this->assertCount(0, $this->callToActionService->listCallToActions(new ListOptions(), $organisation)->items());
    }
}
