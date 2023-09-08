<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\OrganisationRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function implode;
use function in_array;
use function sprintf;

final class ManageTestResultImportsForOrganisation extends Command
{
    private const ACTION_ENABLE = 'enable';
    private const ACTION_DISABLE = 'disable';

    /** @var string */
    protected $signature = 'test-result-import:manage
        {action : enable/disable}
        {--organisation= : enable/disable test result imports for a single organisation}
        {--all-organisations : enables/disables test result imports for all organisations}
    ';

    /** @var string */
    protected $description = 'Manage the organisation(s) for which test result reports are processed';

    public function handle(OrganisationRepository $organisationRepository): int
    {
        /** @var string $action */
        $action = $this->argument('action');

        try {
            $this->validateAction($action);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->error($invalidArgumentException->getMessage());
            return self::FAILURE;
        }

        try {
            $this->manageTestResultImports($organisationRepository, $action);
            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
            return self::FAILURE;
        }
    }

    private function validateAction(string $value): void
    {
        $supportedValues = [self::ACTION_ENABLE, self::ACTION_DISABLE];

        if (!in_array($value, $supportedValues, true)) {
            $supportedValueList = implode('/', $supportedValues);

            throw new InvalidArgumentException(
                sprintf('Invalid argument "%s". Supported are: "%s"', $value, $supportedValueList),
            );
        }
    }

    private function manageTestResultImports(OrganisationRepository $organisationRepository, string $action): void
    {
        if ($this->option('organisation') !== null) {
            /** @var string $organisationUuid */
            $organisationUuid = $this->option('organisation');
            $this->updateSingleOrganisation($organisationRepository, $organisationUuid, $action);
            return;
        }

        if ($this->option('all-organisations') || $this->confirmAllOrganisationsUpdate($action)) {
            $this->updateAllOrganisations($organisationRepository, $action);
        }
    }

    private function updateSingleOrganisation(
        OrganisationRepository $organisationRepository,
        string $organisationUuid,
        string $action,
    ): void {
        $organisation = $organisationRepository->getEloquentOrganisationByUuid($organisationUuid);

        if ($organisation === null) {
            throw new RuntimeException(sprintf('Could not find organisation with uuid: "%s"', $organisationUuid));
        }

        $organisation->isAllowedToReportTestResults = $this->isEnableAction($action);
        $organisation->save();

        $this->info(
            sprintf(
                'Successfully %s test result imports for organisation: "%s"',
                $this->makeActionExecutedLabel($action),
                $organisation->uuid,
            ),
        );
    }

    private function updateAllOrganisations(OrganisationRepository $organisationRepository, string $action): void
    {
        $organisations = $organisationRepository->getAll();

        DB::transaction(
            function () use ($organisations, $action): void {
                foreach ($organisations as $organisation) {
                    $organisation->isAllowedToReportTestResults = $this->isEnableAction($action);
                    $organisation->save();
                }
            },
        );

        $this->info(
            sprintf(
                'Successfully %s test result imports for all organisations',
                $this->makeActionExecutedLabel($action),
            ),
        );
    }

    private function confirmAllOrganisationsUpdate(string $action): bool
    {
        return $this->confirm(
            sprintf(
                'This will %s test result imports for all organisations, do you wish to continue?',
                $action,
            ),
        );
    }

    private function isEnableAction(string $action): bool
    {
        return $action === self::ACTION_ENABLE;
    }

    private function makeActionExecutedLabel(string $action): string
    {
        return $this->isEnableAction($action) ? 'enabled' : 'disabled';
    }
}
