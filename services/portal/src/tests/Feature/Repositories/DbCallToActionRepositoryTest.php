<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\CallToAction\ListOptions;
use App\Repositories\DbCallToActionRepository;
use App\Services\Chores\ChoreService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('call-to-action')]
class DbCallToActionRepositoryTest extends FeatureTestCase
{
    private DbCallToActionRepository $dbCallToActionRepository;
    private ChoreService $choreService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbCallToActionRepository = $this->app->get(DbCallToActionRepository::class);
        $this->choreService = $this->app->get(ChoreService::class);
    }

    public function testListCallToActions(): void
    {
        $this->be($this->createUser());

        $expiresAt = CarbonImmutable::now()->addMonth();
        $callToActionAttributes = ['subject' => $this->faker->text(50)];
        $callToAction = $this->createCallToAction($callToActionAttributes);
        $organisation = $this->createOrganisation();
        $ownerResource = $this->createResourceForCallToAction($callToAction);
        $listOptions = new ListOptions();

        $this->assertDatabaseHas('call_to_action', $callToActionAttributes);

        $list = $this->dbCallToActionRepository->listCallToActions($listOptions, $organisation);
        $this->assertEquals(0, $list->total());

        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'expires_at' => $expiresAt,
        ]);

        $list = $this->dbCallToActionRepository->listCallToActions($listOptions, $organisation);
        $this->assertEquals(1, $list->total());
    }

    public function testListCallToActionDoesNotListCallToActionsWhichAreNotAssignedToAuthenticatedUser(): void
    {
        $authenticatedUser = $this->createUser();
        $this->be($authenticatedUser);

        $otherUser = $this->createUser();
        $organisation = $this->createOrganisation();

        $authenticatedCallToAction = $this->createCallToAction();
        $authenticatedOwnerResource = $this->createResourceForCallToAction($authenticatedCallToAction);

        $otherCallToAction = $this->createCallToAction();
        $otherOwnerResource = $this->createResourceForCallToAction($otherCallToAction);

        $listOptions = new ListOptions();

        $authenticatedUserAssignedChore = $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $authenticatedOwnerResource->type,
            'owner_resource_id' => $authenticatedOwnerResource->id,
        ]);

        $this->createAssignmentWithUserForChore($authenticatedUser, $authenticatedUserAssignedChore);

        $otherUserAssignedChore = $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $otherOwnerResource->type,
            'owner_resource_id' => $otherOwnerResource->id,
        ]);

        $this->createAssignmentWithUserForChore($otherUser, $otherUserAssignedChore);

        $list = $this->dbCallToActionRepository->listCallToActions($listOptions, $organisation);

        $this->assertEquals(1, $list->total());
        $this->assertEquals($authenticatedUserAssignedChore->owner_resource_id, $list->items()[0]->uuid);
    }

    public function testGetCallToActionWhichAreNotAssigned(): void
    {
        $authenticatedUser = $this->createUser();
        $this->be($authenticatedUser);

        $this->createUser();
        $organisation = $this->createOrganisation();

        $authenticatedCallToAction = $this->createCallToAction();
        $authenticatedOwnerResource = $this->createResourceForCallToAction($authenticatedCallToAction);

        $otherCallToAction = $this->createCallToAction();
        $otherOwnerResource = $this->createResourceForCallToAction($otherCallToAction);

        $listOptions = new ListOptions();

        $authenticatedUserAssignedChore = $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $authenticatedOwnerResource->type,
            'owner_resource_id' => $authenticatedOwnerResource->id,
        ]);

        $this->createAssignmentWithUserForChore($authenticatedUser, $authenticatedUserAssignedChore);

        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $otherOwnerResource->type,
            'owner_resource_id' => $otherOwnerResource->id,
        ]);

        $list = $this->dbCallToActionRepository->getUnAssignedCallToActions($listOptions, $organisation);

        $this->assertEquals(1, $list->count());
        $this->assertNull($list->first()->chore->assignment);
    }

    public function testListCallToActionDoesNotShowExpiredCallToActions(): void
    {
        $this->be($this->createUser());

        $expiresAt = $this->faker->dateTimeBetween(
            '-' . (DbCallToActionRepository::DAYS_VISIBLE_AFTER_EXPIRY + 1) . ' months',
            '-' . DbCallToActionRepository::DAYS_VISIBLE_AFTER_EXPIRY . ' days',
        );
        $callToAction = $this->createCallToAction();
        $organisation = $this->createOrganisation();
        $ownerResource = $this->createResourceForCallToAction($callToAction);
        $listOptions = new ListOptions();

        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'expires_at' => $expiresAt,
        ]);

        $list = $this->dbCallToActionRepository->listCallToActions($listOptions, $organisation);
        $this->assertEquals(0, $list->total());
    }

    public function testListCallToActionDoesNotShowCompletedCallToActions(): void
    {
        $this->be($this->createUser());

        $deletedAt = CarbonImmutable::now()->subDay();
        $callToAction = $this->createCallToAction();
        $organisation = $this->createOrganisation();
        $ownerResource = $this->createResourceForCallToAction($callToAction);
        $listOptions = new ListOptions();

        $this->createChoreForOrganisation($organisation, [
            'owner_resource_type' => $ownerResource->type,
            'owner_resource_id' => $ownerResource->id,
            'deleted_at' => $deletedAt,
        ]);

        $list = $this->dbCallToActionRepository->listCallToActions($listOptions, $organisation);
        $this->assertEquals(0, $list->total());
    }
}
