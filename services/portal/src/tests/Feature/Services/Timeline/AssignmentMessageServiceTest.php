<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Timeline;

use App\Services\Timeline\AssignmentChangeBuilder;
use App\Services\Timeline\AssignmentMessageService;
use Carbon\CarbonImmutable;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function app;
use function array_merge;

class AssignmentMessageServiceTest extends FeatureTestCase
{
    #[DataProvider('assignmentMessagesProvider')]
    public function testAssignmentMessages(
        array $prevAssignmentParameters,
        array $newAssignmentParameters,
        array $expectInOrder,
    ): void {
        [$changes, $user] = $this->getAssignmentChanges($prevAssignmentParameters, $newAssignmentParameters);

        /** @var AssignmentMessageService $messageService */
        $messageService = app(AssignmentMessageService::class);

        $message = '';
        foreach ($changes as $change) {
            $message .= $messageService->buildMessage($change, $user);
        }

        PHPUnit::assertThat($expectInOrder, new SeeInOrder($message));
    }

    public static function assignmentMessagesProvider(): array
    {
        return [
            'externe user > eigen organisatie' => [
                ['assigned_user_uuid' => 'ext_user', 'assigned_organisation_uuid' => 'ext_org'],
                ['assigned_user_uuid' => null, 'assigned_organisation_uuid' => null],
                [
                    'Medewerker: <b>Een medewerker van Ext org</b> naar <b>geen toewijzing</b>',
                    'Organisatie: van <b>Ext org</b> naar <b>Thuis org</b>',
                ],
            ],
            'null > user' => [
                [],
                ['assigned_user_uuid' => 'user'],
                ['Medewerker: <b>geen toewijzing</b> naar <b>Thuis user</b>'],
            ],
            'User > werkverdeler' => [
                ['assigned_user_uuid' => 'user'],
                ['assigned_user_uuid' => 'planner'],
                ['Medewerker: <b>Thuis user</b> naar <b>Werkverdeler</b>'],
            ],
            'Werkverdeler > externe org' => [
                ['assigned_user_uuid' => 'planner'],
                ['assigned_user_uuid' => null, 'assigned_organisation_uuid' => 'ext_org'],
                [
                    'Medewerker: <b>Werkverdeler</b> naar <b>geen toewijzing</b>',
                    'Organisatie: van <b>Thuis org</b> naar <b>Ext org</b>',
                ],
            ],
            'externe org > externe org + externe lijst' => [
                ['assigned_organisation_uuid' => 'ext_org'],
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_case_list_uuid' => 'ext_list'],
                ['Lijst: <b>geen lijst</b> naar <b>een lijst van Ext org</b>'],
            ],
            'null > externe lijst' => [
                [],
                ['assigned_case_list_uuid' => 'ext_list'],
                ['Lijst: <b>geen lijst</b> naar <b>een lijst van Ext org</b>'],
            ],
            'externe lijst > externe user' => [
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_case_list_uuid' => 'ext_list'],
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_user_uuid' => 'ext_user'],
                [
                    'Medewerker: <b>geen toewijzing</b> naar <b>Een medewerker van Ext org</b>',
                    'Lijst: <b>een lijst van Ext org</b> naar <b>geen lijst</b>',
                ],
            ],
            'null > wachtrij' => [
                [],
                ['assigned_case_list_uuid' => 'queue'],
                ['Lijst: <b>geen lijst</b> naar <b>wachtrij</b>'],
            ],
            'wachtrij > lijst' => [
                ['assigned_case_list_uuid' => 'queue'],
                ['assigned_case_list_uuid' => 'list'],
                ['Lijst: <b>wachtrij</b> naar <b>Mylist</b>'],
            ],
        ];
    }

    #[DataProvider('listHistoryPreservedWhenListDeletedProvider')]
    public function testListHistoryPreservedWhenListDeleted(bool $isQueue, string $expected): void
    {
        /** @var AssignmentMessageService $messageService */
        $messageService = app(AssignmentMessageService::class);

        /** @var AssignmentChangeBuilder $changeBuilder */
        $changeBuilder = app(AssignmentChangeBuilder::class);

        // fixture
        $organisation = $this->createOrganisation(['uuid' => 'org', 'name' => 'Thuis org']);
        $user = $this->createUserForOrganisation($organisation, ['uuid' => 'user', 'name' => 'Thuis user']);
        $case = $this->createCaseForUser($user);

        $list = $this->createCaseListForOrganisation(
            $organisation,
            ['uuid' => 'list', 'name' => 'Mylist', 'is_queue' => $isQueue, 'is_default' => false],
        );

        // because of CaseListAuthScope
        $this->be($user);

        $previousAssignment = $this->createAssignmentHistoryForCase(
            $case,
            [
                'assigned_at' => CarbonImmutable::now(),
                'assigned_user_uuid' => null,
                'assigned_organisation_uuid' => null,
                'assigned_case_list_uuid' => $list->uuid,
            ],
        );

        $newAssignment = $this->createAssignmentHistoryForCase(
            $case,
            [
                'assigned_at' => CarbonImmutable::now(),
                'assigned_user_uuid' => null,
                'assigned_organisation_uuid' => null,
                'assigned_case_list_uuid' => null,
            ],
        );

        $case->assigned_case_list_uuid = null;
        $case->save();
        $list->delete();

        $changes = $changeBuilder->getAssignmentChanges($newAssignment, $previousAssignment);

        $message = '';
        foreach ($changes as $change) {
            $message .= $messageService->buildMessage($change, $user);
        }

        $this->assertStringContainsString($expected, $message);
    }

    public static function listHistoryPreservedWhenListDeletedProvider(): array
    {
        return [
            'no queue' => [
                false,
                'Lijst: <b>Mylist</b> naar <b>geen lijst</b>',
            ],
            'with queue' => [
                true,
                'Lijst: <b>wachtrij</b> naar <b>geen lijst</b>',
            ],
        ];
    }

    #[DataProvider('assignmentConflictMessagesProvider')]
    public function testAssignmentConflictMessages(
        array $prevAssignmentParameters,
        array $newAssignmentParameters,
        array $expectInOrder,
    ): void {
        [$changes, $user] = $this->getAssignmentChanges($prevAssignmentParameters, $newAssignmentParameters);

        /** @var AssignmentMessageService $messageService */
        $messageService = app(AssignmentMessageService::class);

        $message = '';
        foreach ($changes as $change) {
            $message .= $messageService->buildConflictMessage($change, $user);
        }

        PHPUnit::assertThat($expectInOrder, new SeeInOrder($message));
    }

    private function getAssignmentChanges(
        array $prevAssignmentParameters,
        array $newAssignmentParameters,
    ): array {
        /** @var AssignmentChangeBuilder $changeBuilder */
        $changeBuilder = app(AssignmentChangeBuilder::class);

        $namedAttributes = [];

        // fixture
        $organisation = $this->createOrganisation(['uuid' => 'org', 'name' => 'Thuis org']);
        $user = $this->createUserForOrganisation($organisation, ['uuid' => 'user', 'name' => 'Thuis user']);
        $case = $this->createCaseForUser($user);
        $this->createUserForOrganisation($organisation, ['uuid' => 'planner', 'name' => 'Werkverdeler'], 'planner');

        $namedAttributes[] = $this->createCaseListForOrganisation(
            $organisation,
            ['uuid' => 'queue', 'is_queue' => true, 'is_default' => true],
        );

        $namedAttributes[] = $this->createCaseListForOrganisation(
            $organisation,
            ['uuid' => 'list', 'name' => 'Mylist', 'is_queue' => false, 'is_default' => false],
        );

        // External fixture
        $externalOrg = $this->createOrganisation(['uuid' => 'ext_org', 'name' => 'Ext org']);
        $this->createUserForOrganisation($externalOrg, ['uuid' => 'ext_user', 'name' => 'Ext user']);

        $namedAttributes[] = $this->createCaseListForOrganisation(
            $externalOrg,
            ['uuid' => 'ext_queue', 'is_queue' => true, 'is_default' => true],
        );

        $namedAttributes[] = $this->createCaseListForOrganisation(
            $externalOrg,
            ['uuid' => 'ext_list', 'name' => 'Extlist', 'is_queue' => false, 'is_default' => false],
        );

        // because of CaseListAuthScope
        $this->be($user);

        $previousAssignment = $this->createAssignmentHistoryForCase(
            $case,
            array_merge([
                'assigned_at' => CarbonImmutable::now(),
                'assigned_user_uuid' => null,
                'assigned_organisation_uuid' => null,
                'assigned_case_list_uuid' => null,
            ], $prevAssignmentParameters),
        );

        $newAssignment = $this->createAssignmentHistoryForCase(
            $case,
            array_merge([
                'assigned_at' => CarbonImmutable::now(),
                'assigned_user_uuid' => null,
                'assigned_organisation_uuid' => null,
                'assigned_case_list_uuid' => null,
            ], $newAssignmentParameters),
        );

        foreach ($namedAttributes as $namedAttribute) {
            $namedAttribute->name = 'random string';
            $namedAttribute->save();
        }

        $changes = $changeBuilder->getAssignmentChanges($newAssignment, $previousAssignment);

        return [$changes, $user];
    }

    public static function assignmentConflictMessagesProvider(): array
    {
        return [
            'externe user > eigen organisatie' => [
                ['assigned_user_uuid' => 'ext_user', 'assigned_organisation_uuid' => 'ext_org'],
                ['assigned_user_uuid' => null, 'assigned_organisation_uuid' => null],
                [
                    'Toegewezen aan <b> geen toewijzing</b>',
                    'Toegewezen aan <b> Thuis org</b>',
                ],
            ],
            'null > user' => [
                [],
                ['assigned_user_uuid' => 'user'],
                ['Toegewezen aan <b> Thuis user</b>'],
            ],
            'User > werkverdeler' => [
                ['assigned_user_uuid' => 'user'],
                ['assigned_user_uuid' => 'planner'],
                ['Toegewezen aan <b> Werkverdeler</b>'],
            ],
            'Werkverdeler > externe org' => [
                ['assigned_user_uuid' => 'planner'],
                ['assigned_user_uuid' => null, 'assigned_organisation_uuid' => 'ext_org'],
                [
                    'Toegewezen aan <b> geen toewijzing</b>',
                    'Toegewezen aan <b> Ext org</b>',
                ],
            ],
            'externe org > externe org + externe lijst' => [
                ['assigned_organisation_uuid' => 'ext_org'],
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_case_list_uuid' => 'ext_list'],
                ['Verplaatst naar <b> een lijst van Ext org</b>'],
            ],
            'null > externe lijst' => [
                [],
                ['assigned_case_list_uuid' => 'ext_list'],
                ['Verplaatst naar <b> een lijst van Ext org</b>'],
            ],
            'externe lijst > externe user' => [
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_case_list_uuid' => 'ext_list'],
                ['assigned_organisation_uuid' => 'ext_org', 'assigned_user_uuid' => 'ext_user'],
                [
                    'Toegewezen aan <b> Een medewerker van Ext org</b>',
                    'Verplaatst naar <b> geen lijst</b>',
                ],
            ],
            'null > wachtrij' => [
                [],
                ['assigned_case_list_uuid' => 'queue'],
                ['Verplaatst naar <b> wachtrij</b>'],
            ],
            'wachtrij > lijst' => [
                ['assigned_case_list_uuid' => 'queue'],
                ['assigned_case_list_uuid' => 'list'],
                ['Verplaatst naar <b> Mylist</b>'],
            ],
        ];
    }
}
