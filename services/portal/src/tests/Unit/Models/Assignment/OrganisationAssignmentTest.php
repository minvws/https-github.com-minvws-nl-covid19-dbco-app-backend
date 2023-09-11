<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Assignment;

use App\Models\Assignment\OrganisationAssignment;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Generator;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganisationAssignmentTest extends TestCase
{
    #[DataProvider('isValidForSelectedOrganisationProvider')]
    public function testIsValidForSelectedOrganisation(
        bool $expected,
        bool $outsourcingEnabled,
        bool $outsourcingToRegionalGgdEnabled,
        OrganisationType $organisationType,
        bool $isOutsourceOrganisation,
        bool $validateFull,
        bool $isOrganisationThatOutsourcesTo,
        bool $isAvailableForOutsourcing,
    ): void {
        // Given outsourcing is (not) enabled
        Config::set('featureflag.outsourcing_enabled', $outsourcingEnabled);
        // And outsourcing to regional GGDs is (not) enabled
        Config::set('featureflag.outsourcing_to_regional_ggd_enabled', $outsourcingToRegionalGgdEnabled);

        $organisation = $this->mock(
            EloquentOrganisation::class,
            static function (MockInterface $mock) use ($organisationType, $isAvailableForOutsourcing): void {
                // And the target organisation has organisation type <type>
                $mock->allows('getAttribute')
                    ->with('type')
                    ->andReturns($organisationType);
                // And the target organisation is (not) available for outsourcing
                $mock->allows('getAttribute')
                    ->with('isAvailableForOutsourcing')
                    ->andReturns($isAvailableForOutsourcing);
            },
        );

        $selectedOrganisation = $this->mock(
            EloquentOrganisation::class,
            static function (MockInterface $mock) use ($organisation, $isOutsourceOrganisation, $isOrganisationThatOutsourcesTo): void {
                // And the selected organisation is (not) an outsource organisation
                $mock->allows('isOutsourceOrganisation')
                    ->andReturns($isOutsourceOrganisation);
                // And the selected organisation (outsources|does not outsource) to a target organisation
                $mock->allows('isOrganisationThatOutsourcesTo')
                    ->with($organisation)
                    ->andReturns($isOrganisationThatOutsourcesTo);
            },
        );

        $assignment = new OrganisationAssignment($organisation);
        // When the validity of the organisation assignment is checked
        $actual = $assignment->isValidForSelectedOrganisation($selectedOrganisation, $validateFull);

        // Then the organisation assignment should (not) be valid
        $this->assertEquals($expected, $actual);
    }

    public static function isValidForSelectedOrganisationProvider(): Generator
    {
        yield 'Invalid assignment if outsourcing is disabled' => [false, false, true, OrganisationType::regionalGGD(), false, false, false, true];
        yield 'Invalid assignment to regional GGD if outsourcing to regional is disabled' => [false, true, false, OrganisationType::regionalGGD(), false, false, false, true];
        yield 'Valid assignment to outsource organisation if outsourcing to regional is disabled' => [true, true, false, OrganisationType::outsourceOrganisation(), false, false, false, true];
        yield 'Invalid assignment if attempted by outsource organisation' => [false, true, false, OrganisationType::outsourceOrganisation(), true, false, false, true];
        yield 'Valid assignment if not fully validating' => [true, true, true, OrganisationType::regionalGGD(), false, false, false, true];
        yield 'Invalid assignment to organisation that is available for outsourcing and has no outsourcing relation' => [false, true, true, OrganisationType::regionalGGD(), false, true, false, true];
        yield 'Invalid assignment to organisation that is not available for outsourcing' => [false, true, true, OrganisationType::regionalGGD(), false, true, false, false];
    }
}
