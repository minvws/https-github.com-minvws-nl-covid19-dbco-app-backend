<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Assignment\Cache;
use App\Models\Assignment\NullOrganisationAssignment;
use App\Models\Assignment\OrganisationAssignment;
use App\Models\Assignment\ReturnToOwnerOption;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use App\Services\AuthenticationService;
use App\Services\CaseAssignmentService;
use Faker\Provider\Uuid;
use Generator;
use Illuminate\Support\Facades\Config;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function app;
use function collect;

class CaseAssignmentServiceTest extends TestCase
{
    private const CONFIG_KEY_OUTSOURCING_ENABLED = 'featureflag.outsourcing_enabled';
    private const CONFIG_KEY_OUTSOURCING_REGIONAL_ENABLED = 'featureflag.outsourcing_to_regional_ggd_enabled';

    private bool $outsourcingEnabled;
    private bool $outsourcingRegionalEnabled;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outsourcingEnabled = Config::get(self::CONFIG_KEY_OUTSOURCING_ENABLED);
        $this->outsourcingRegionalEnabled = Config::get(self::CONFIG_KEY_OUTSOURCING_REGIONAL_ENABLED);
    }

    protected function tearDown(): void
    {
        Config::set(self::CONFIG_KEY_OUTSOURCING_ENABLED, $this->outsourcingEnabled);
        Config::set(self::CONFIG_KEY_OUTSOURCING_REGIONAL_ENABLED, $this->outsourcingRegionalEnabled);

        parent::tearDown();
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationUnassignmentByAssignedOrganisation(): void
    {
        $requiredSelectedOrganisationUuid = $this->mockGetRequiredSelectedOrganisation();

        $case = new EloquentCase([]);
        $case->assigned_organisation_uuid = $requiredSelectedOrganisationUuid;

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new NullOrganisationAssignment());
        $message = 'Assigned Organisation can give Case back.';

        $this->assertTrue($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationUnassignmentByOwnerOrganisation(): void
    {
        $requiredSelectedOrganisationUuid = $this->mockGetRequiredSelectedOrganisation();

        $case = new EloquentCase([]);
        $case->assigned_organisation_uuid = Uuid::uuid();
        $case->organisation_uuid = $requiredSelectedOrganisationUuid;
        $case->assigned_user_uuid = null;

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new NullOrganisationAssignment());
        $message = 'Owner Organisation can unassign Case from assigned to an Organisation if no User is assigned.';

        $this->assertTrue($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationAssignmentInvalidUnassignment(): void
    {
        $case = new EloquentCase([]);
        $case->organisation_uuid = Uuid::uuid();

        $requiredSelectedOrganisationUuid = $this->mockGetRequiredSelectedOrganisation();

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new NullOrganisationAssignment());
        $message = 'Users may not unassign Case owned by another Organisation.';

        $this->assertFalse($actual, $message);

        $case->assigned_user_uuid = Uuid::uuid();
        $case->organisation_uuid = $requiredSelectedOrganisationUuid;

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new NullOrganisationAssignment());
        $message = 'Users may not assign a Case owned by their Organisation while it is assigned to a User.';

        $this->assertFalse($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationAssignmentCaseWaitingForApproval(): void
    {
        $this->mockGetRequiredSelectedOrganisation();

        $case = new EloquentCase([]);
        $case->is_approved = null;
        $case->bco_status = BCOStatus::completed();

        $organisation = new EloquentOrganisation();

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new OrganisationAssignment($organisation));
        $message = 'A Case that is waiting for approval cannot be assigned to an Organisation.';

        $this->assertFalse($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationAssignmentCaseAssignedToUser(): void
    {
        $requiredSelectedOrganisationUuid = $this->mockGetRequiredSelectedOrganisation();

        $case = new EloquentCase([]);
        $case->organisation_uuid = $requiredSelectedOrganisationUuid;
        $case->assigned_organisation_uuid = Uuid::uuid();
        $case->assigned_user_uuid = Uuid::uuid();

        $organisation = new EloquentOrganisation();

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new OrganisationAssignment($organisation));
        $message = 'User from owner Organisation cannot assign Case from one Organisation to another if a User is assigned.';

        $this->assertFalse($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationAssignmentOutsourcing(): void
    {
        $requiredSelectedOrganisationUuid = $this->mockGetRequiredSelectedOrganisation();

        Config::set('featureflag.outsourcing_enabled', true);

        $organisation = new EloquentOrganisation();
        $organisation->uuid = Uuid::uuid();
        $organisation->type = OrganisationType::outsourceDepartment();

        $case = new EloquentCase([]);
        $case->organisation_uuid = $requiredSelectedOrganisationUuid;

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new OrganisationAssignment($organisation), false);
        $message = 'User from Owner Organisation can (re-)assign Case to a partner Organisation.';

        $this->assertTrue($actual, $message);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationAssignmentOtherWithOrganisation(): void
    {
        $organisation = new EloquentOrganisation();

        $case = new EloquentCase([]);
        $case->organisation_uuid = Uuid::uuid();

        $this->mockGetRequiredSelectedOrganisation();

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new OrganisationAssignment($organisation));
        $message = 'Case owned by another Organisation should not be valid.';

        $this->assertFalse($actual, $message);

        $case->organisation_uuid = null;

        $actual = app(CaseAssignmentService::class)->isValidAssignment($case, new OrganisationAssignment($organisation));
        $message = 'Case not owned by any Organisation should not be valid.';

        $this->assertFalse($actual, $message);
    }

    #[Group('case-assignment')]
    #[Group('cax')]
    public function testIsValidOrganisationWithOutsourcingDisabled(): void
    {
        $requiredSelectedOrganisation = new EloquentOrganisation();
        $requiredSelectedOrganisation->uuid = $this->mockGetRequiredSelectedOrganisation();

        Config::set('featureflag.outsourcing_enabled', false);

        $organisation = new EloquentOrganisation();
        $organisation->uuid = Uuid::uuid();

        $assignment = new OrganisationAssignment($organisation);
        $actual = $assignment->isValidForSelectedOrganisation($requiredSelectedOrganisation, false);

        $this->assertFalse($actual);
    }

    #[Group('case-assignment')]
    public function testIsValidOrganisationCalledWithEloquentOrganisation(): void
    {
        $requiredSelectedOrganisation = new EloquentOrganisation();
        $requiredSelectedOrganisation->uuid = $this->mockGetRequiredSelectedOrganisation();

        Config::set('featureflag.outsourcing_enabled', true);
        Config::set('featureflag.outsourcing_to_regional_ggd_enabled', false);

        $organisation = new EloquentOrganisation();
        $organisation->uuid = Uuid::uuid();
        $organisation->type = OrganisationType::outsourceDepartment();

        $assignment = new OrganisationAssignment($organisation);
        $actual = $assignment->isValidForSelectedOrganisation($requiredSelectedOrganisation, false);

        $this->assertTrue($actual);
    }

    #[DataProvider('isValidOrganisationProvider')]
    #[Group('case-assignment')]
    public function testIsValidOrganisationCalledWithStringOrganisation(
        bool $regionalOutsourcingEnabled,
        bool $isAvailableForOutsourcing,
        ?OrganisationType $organisationType,
        bool $isValid,
    ): void {
        Config::set('featureflag.outsourcing_enabled', true);
        Config::set('featureflag.outsourcing_to_regional_ggd_enabled', $regionalOutsourcingEnabled);

        $requiredSelectedOrganisationUuid = Uuid::uuid();

        $requiredSelectedOrganisation = new EloquentOrganisation();
        $requiredSelectedOrganisation->uuid = $requiredSelectedOrganisationUuid;

        $this->createAuthenticationServiceMockWithOrganisation($requiredSelectedOrganisation);

        $organisation = new EloquentOrganisation();
        $organisation->uuid = Uuid::uuid();
        $organisation->type = $organisationType;
        $organisation->is_available_for_outsourcing = $isAvailableForOutsourcing;

        $requiredSelectedOrganisation->outsourceOrganisations = collect([$organisation->uuid]);

        $assignment = new OrganisationAssignment($organisation);
        $actual = $assignment->isValidForSelectedOrganisation($requiredSelectedOrganisation, true);

        $this->assertEquals($isValid, $actual);
    }

    /**
     * @see testIsValidOrganisationCalledWithStringOrganisation
     */
    public static function isValidOrganisationProvider(): Generator
    {
        yield 'config on & regional GGD & available for outsourcing' => [
            true,
            true,
            OrganisationType::regionalGGD(),
            true,
        ];
        yield 'config on & regional GGD & unavailable for outsourcing' => [
            true,
            false,
            OrganisationType::regionalGGD(),
            false,
        ];
        yield 'config off & regional GGD' => [false, true, OrganisationType::regionalGGD(), false];
        yield 'config on & outsource department' => [true, true, OrganisationType::outsourceDepartment(), true];
        yield 'config off & outsource department' => [false, true, OrganisationType::outsourceDepartment(), true];
    }

    private function mockGetRequiredSelectedOrganisation(): string
    {
        $requiredSelectedOrganisationUuid = Uuid::uuid();
        $requiredSelectedOrganisation = new EloquentOrganisation();
        $requiredSelectedOrganisation->uuid = $requiredSelectedOrganisationUuid;

        $this->createAuthenticationServiceMockWithOrganisation($requiredSelectedOrganisation);

        return $requiredSelectedOrganisationUuid;
    }

    private function createAuthenticationServiceMockWithOrganisation(EloquentOrganisation $organisation): void
    {
        $this->mock(
            AuthenticationService::class,
            static function (MockInterface $mock) use ($organisation): void {
                $mock->allows('getRequiredSelectedOrganisation')
                    ->andReturn($organisation);
            },
        );
    }

    #[DataProvider('updateReturnToOwnerAssignmentOptionForCaseProvider')]
    public function testUpdateReturnToOwnerAssignmentOptionForCase(
        string $selectedOrganisationUuid,
        string $ownerOrganisationUuid,
        ?string $assignedOrganisationUuid,
        ?string $assignedUserUuid,
        bool $isEnabled,
    ): void {
        $requiredSelectedOrganisation = new EloquentOrganisation();
        $requiredSelectedOrganisation->uuid = $selectedOrganisationUuid;

        $ownerOrganisation = new EloquentOrganisation();
        $ownerOrganisation->uuid = $ownerOrganisationUuid;

        $this->createAuthenticationServiceMockWithOrganisation($requiredSelectedOrganisation);

        $option = new ReturnToOwnerOption();

        $case = new EloquentCase();
        $case->organisation_uuid = $ownerOrganisationUuid;
        $case->assigned_organisation_uuid = $assignedOrganisationUuid;
        $case->assigned_user_uuid = $assignedUserUuid;
        $case->organisation = $ownerOrganisation;

        $option->updateForCase($case, $requiredSelectedOrganisation, false, new Cache());

        $this->assertEquals($isEnabled, $option->isEnabled());
    }

    public static function updateReturnToOwnerAssignmentOptionForCaseProvider(): Generator
    {
        yield 'owned by current organisation, not assigned' => [
            '11111111-1111-1111-1111-111111111111',
            '11111111-1111-1111-1111-111111111111',
            null,
            null,
            false,
        ];
        yield 'owned by current organisation, assigned to different organisation' => [
            '11111111-1111-1111-1111-111111111111',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            null,
            true,
        ];
        yield 'owned by current organisation, assigned to user' => [
            '11111111-1111-1111-1111-111111111111',
            '11111111-1111-1111-1111-111111111111',
            null,
            '33333333-3333-3333-3333-333333333333',
            false,
        ];
        yield 'owned by current organisation, assigned to different organisation, assigned to user' => [
            '11111111-1111-1111-1111-111111111111',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            false,
        ];
        yield 'owned by different organisation, assigned to current organisation' => [
            '22222222-2222-2222-2222-222222222222',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            null,
            true,
        ];
        yield 'owned by different organisation, assigned to current organisation, assigned to user' => [
            '22222222-2222-2222-2222-222222222222',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            true,
        ];
    }
}
