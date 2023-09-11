<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Repositories\AuditRepository;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function collect;
use function sprintf;

#[Group('planner-case')]
class ApiPlannerCaseControllerAuditTest extends FeatureTestCase
{
    public function testCreatePlannerCaseLogsIfLabelOrPrioIsSet(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $auditRepository = $this->spy(AuditRepository::class);

        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $caseLabel = $this->createCaseLabelForOrganisation($organisation);

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
            'caseLabels' => [
                $caseLabel->uuid,
            ],
            'priority' => Priority::none()->value,
        ];

        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(201);
        $this->assertAuditDetailSet($auditRepository, 'labelsUpdated');
        $this->assertAuditDetailSet($auditRepository, 'priorityUpdated');
    }

    public function testCreatePlannerCaseDoesNotLogIfLabelOrPrioAreNotSet(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $auditRepository = $this->spy(AuditRepository::class);

        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ];

        $response = $this->be($user)->postJson('/api/cases', $payload);

        $this->assertAuditDetailNotSet($auditRepository, 'labelsUpdated');
        $this->assertAuditDetailNotSet($auditRepository, 'priorityUpdated');
        $response->assertStatus(201);
    }

    #[DataProvider('updatePlannerCaseLogsIfLabelOrPrioIsUpdatedDataProvider')]
    public function testUpdatePlannerCaseLogsIfLabelOrPrioIsUpdated(array $payload, array $detailsSet, array $detailsNotSet): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $auditRepository = $this->spy(AuditRepository::class);

        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
                'date_of_test' => null,
                'priority' => Priority::high(),
            ],
        );

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation, ['uuid' => 'foo', 'label' => 'foo']);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation, ['uuid' => 'bar', 'label' => 'bar']);
        $this->createCaseLabelForOrganisation($organisation, ['uuid' => 'baz', 'label' => 'baz']);

        // attach label1 and label2 to case
        $case->caseLabels()->attach($caseLabel1->uuid);
        $case->caseLabels()->attach($caseLabel2->uuid);

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), $payload);
        $response->assertStatus(200);

        foreach ($detailsSet as $detail) {
            $this->assertAuditDetailSet($auditRepository, $detail);
        }
        foreach ($detailsNotSet as $detail) {
            $this->assertAuditDetailNotSet($auditRepository, $detail);
        }
    }

    public static function updatePlannerCaseLogsIfLabelOrPrioIsUpdatedDataProvider(): array
    {
        return [
            'priority stays the same' => [
                [
                    'priority' => Priority::high()->value,
                ],
                [],
                ['priorityUpdated'],
            ],
            'priority changes' => [
                [
                    'priority' => Priority::none()->value,
                ],
                ['priorityUpdated'],
                [],
            ],
            'labels stay the same' => [
                [
                    'caseLabels' => ['foo', 'bar'],
                ],
                [],
                ['labelsUpdated'],
            ],
            'labels are changed' => [
                [
                    'caseLabels' => ['baz'],
                ],
                ['labelsUpdated'],
                [],
            ],
            'labels are cleared' => [
                [
                    'caseLabels' => [],
                ],
                ['labelsUpdated'],
                [],
            ],
        ];
    }

    public function testSearchCaseNoResultIsLoggedToAuditLog(): void
    {
        $planner = $this->createUser([], 'planner');

        $searchValue = '123123';
        $auditRepository = $this->spy(AuditRepository::class);

        $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $searchValue,
        ]);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event) use ($searchValue) {
                /** @var AuditObject $object */
                $object = $this->getCaseObjectFromAuditEvent($event);
                if ($object === null) {
                    return false;
                }
                return $object->getIdentifier() === 'identifier: ' . $searchValue;
            }));
    }

    public function testSearchCaseIsLoggedToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class)->makePartial();

        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForUser($planner, [
            'created_at' => CarbonImmutable::now(),
            'hpzone_number' => '1234567',
            'bco_status' => BCOStatus::open(),
        ]);

        $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $case->caseId,
        ]);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event) use ($case) {
                /** @var AuditObject $object */
                $object = $this->getCaseObjectFromAuditEvent($event);
                if ($object === null) {
                    return false;
                }

                $auditUuid = $object->getDetails()['uuid'] ?? null;
                $hpzoneId = $object->getDetails()['hpzoneId'] ?? null;

                return $object->getIdentifier() === 'identifier: ' . $case->caseId
                    && $auditUuid === $case->uuid
                    && $hpzoneId === $case->hpzoneNumber;
            }));
    }

    private function assertAuditDetailSet(MockInterface $auditRepository, string $detailKey): void
    {
        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event) use ($detailKey) {
                /** @var AuditObject $object */
                $object = $this->getCaseObjectFromAuditEvent($event);
                if ($object === null) {
                    return false;
                }
                return $object->getDetails()[$detailKey] === true;
            }));
    }

    private function assertAuditDetailNotSet(MockInterface $auditRepository, string $detailKey): void
    {
        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event) use ($detailKey) {
                /** @var AuditObject $object */
                $object = $this->getCaseObjectFromAuditEvent($event);
                if ($object === null) {
                    return false;
                }
                return !isset($object->getDetails()[$detailKey]);
            }));
    }

    private function getCaseObjectFromAuditEvent(AuditEvent $event): ?AuditObject
    {
        return collect($event->getObjects())->first(static fn(AuditObject $object) => $object->getType() === 'case');
    }
}
