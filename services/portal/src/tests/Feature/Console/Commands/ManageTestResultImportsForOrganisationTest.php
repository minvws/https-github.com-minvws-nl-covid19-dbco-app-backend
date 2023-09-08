<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ManageTestResultImportsForOrganisationTest extends FeatureTestCase
{
    public function testCommandFailsForInvalidArgument(): void
    {
        $this->artisan('test-result-import:manage foo')
            ->assertFailed()
            ->expectsOutput('Invalid argument "foo". Supported are: "enable/disable"')
            ->execute();
    }

    public function testEnableForNonExistingOrganisation(): void
    {
        $this->artisan('test-result-import:manage enable --organisation="foo"')
            ->assertFailed()
            ->expectsOutput('Could not find organisation with uuid: "foo"')
            ->execute();
    }

    public function testDisableForNonExistingOrganisation(): void
    {
        $this->artisan('test-result-import:manage disable --organisation="foo"')
            ->assertFailed()
            ->expectsOutput('Could not find organisation with uuid: "foo"')
            ->execute();
    }

    public function testEnableForSingleOrganisation(): void
    {
        $organisation = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);

        $this->artisan(
            sprintf(
                'test-result-import:manage enable --organisation="%s"',
                $organisation->uuid,
            ),
        )
            ->assertSuccessful()
            ->expectsOutput(
                sprintf(
                    'Successfully enabled test result imports for organisation: "%s"',
                    $organisation->uuid,
                ),
            )
            ->execute();

        $organisation->refresh();

        $this->assertTrue($organisation->isAllowedToReportTestResults);
    }

    public function testDisableForSingleOrganisation(): void
    {
        $organisation = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);

        $this->artisan(
            sprintf(
                'test-result-import:manage disable --organisation="%s"',
                $organisation->uuid,
            ),
        )
            ->assertSuccessful()
            ->expectsOutput(
                sprintf(
                    'Successfully disabled test result imports for organisation: "%s"',
                    $organisation->uuid,
                ),
            )
            ->execute();

        $organisation->refresh();

        $this->assertFalse($organisation->isAllowedToReportTestResults);
    }

    public function testExplicitlyEnableForAllOrganisationsWithOption(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);

        $this->artisan('test-result-import:manage enable --all-organisations')
            ->assertSuccessful()
            ->expectsOutput('Successfully enabled test result imports for all organisations')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertTrue($organisation1->isAllowedToReportTestResults);
        $this->assertTrue($organisation2->isAllowedToReportTestResults);
    }

    public function testExplicitlyDisableForAllOrganisationsWithOption(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);

        $this->artisan('test-result-import:manage disable --all-organisations')
            ->assertSuccessful()
            ->expectsOutput('Successfully disabled test result imports for all organisations')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertFalse($organisation1->isAllowedToReportTestResults);
        $this->assertFalse($organisation2->isAllowedToReportTestResults);
    }

    public function testEnableForAllOrganisationsWithConfirmationDialogAnswerYes(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);

        $this->artisan('test-result-import:manage enable')
            ->assertSuccessful()
            ->expectsConfirmation('This will enable test result imports for all organisations, do you wish to continue?', 'yes')
            ->expectsOutput('Successfully enabled test result imports for all organisations')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertTrue($organisation1->isAllowedToReportTestResults);
        $this->assertTrue($organisation2->isAllowedToReportTestResults);
    }

    public function testEnableForAllOrganisationsWithConfirmationDialogAnswerNo(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => false]);

        $this->artisan('test-result-import:manage enable')
            ->assertSuccessful()
            ->expectsConfirmation('This will enable test result imports for all organisations, do you wish to continue?', 'no')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertFalse($organisation1->isAllowedToReportTestResults);
        $this->assertFalse($organisation2->isAllowedToReportTestResults);
    }

    public function testDisableForAllOrganisationsWithConfirmationDialogAnswerYes(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);

        $this->artisan('test-result-import:manage disable')
            ->assertSuccessful()
            ->expectsConfirmation('This will disable test result imports for all organisations, do you wish to continue?', 'yes')
            ->expectsOutput('Successfully disabled test result imports for all organisations')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertFalse($organisation1->isAllowedToReportTestResults);
        $this->assertFalse($organisation2->isAllowedToReportTestResults);
    }

    public function testDisableForAllOrganisationsWithConfirmationDialogAnswerNo(): void
    {
        $organisation1 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);
        $organisation2 = $this->createOrganisation(['is_allowed_to_report_test_results' => true]);

        $this->artisan('test-result-import:manage disable')
            ->assertSuccessful()
            ->expectsConfirmation('This will disable test result imports for all organisations, do you wish to continue?', 'no')
            ->execute();

        $organisation1->refresh();
        $organisation2->refresh();

        $this->assertTrue($organisation1->isAllowedToReportTestResults);
        $this->assertTrue($organisation2->isAllowedToReportTestResults);
    }
}
