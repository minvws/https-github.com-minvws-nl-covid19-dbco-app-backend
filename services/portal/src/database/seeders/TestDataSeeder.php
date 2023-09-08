<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CovidCase\Intake\Abroad;
use App\Models\CovidCase\Intake\Trip;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Services\CaseFragmentService;
use App\Services\TestDataService;
use Closure;
use Exception;
use Illuminate\Database\Seeder;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function ceil;
use function min;
use function now;
use function rand;

/**
 * Seeder to create realistic data in the database.
 *
 * Responsible for:
 * - generating users (if necessary)
 * - generating cases
 * - generating tasks
 * - generating contexts (created in TestDataService)
 */
class TestDataSeeder extends Seeder
{
    protected TestDataService $testDataService;
    protected CaseFragmentService $caseFragmentService;

    public function __construct(
        TestDataService $testDataService,
        CaseFragmentService $caseFragmentService,
    ) {
        $this->testDataService = $testDataService;
        $this->caseFragmentService = $caseFragmentService;
    }

    /**
     * @throws Exception
     */
    public function run(int $amount, array $organisationUuids, Closure $onCaseCreated): void
    {
        $organisations = $this->testDataService->getOrganisations($organisationUuids);
        $amountPerOrganisation = (int) ceil($amount / $organisations->count());

        $offset = 0;
        foreach ($organisations as $organisation) {
            $planner = $this->testDataService->getOrCreateUser($organisation, 'planner');
            $user = $this->testDataService->getOrCreateUser($organisation, 'user');
            $defaultCaseList = $this->testDataService->getOrCreateDefaultCaseList($organisation);
            $caseLists = $this->testDataService->getOrCreateCaseLists($organisation, 5);

            $currentAmount = min($amountPerOrganisation, $amount - $offset);
            $this->testDataService->createCases(
                $organisation,
                $currentAmount,
                $planner,
                $user,
                $defaultCaseList,
                $caseLists,
                $onCaseCreated,
            );

            $this->createCaseCountryToCountry($organisation, $planner);
            $this->createCaseAbroadAndBack($organisation, $planner);
            $this->createCaseAbroadAndBackMultiple($organisation, $planner);

            $offset += $amountPerOrganisation;
        }
    }

    public function createCaseAbroadAndBackMultiple(
        EloquentOrganisation $organisation,
        EloquentUser $planner,
    ): EloquentCase {
        $case = $this->testDataService->createCase($organisation, $planner);

        /** @var Trip $trip1 */
        $trip1 = Trip::getSchema()->getCurrentVersion()->newInstance();
        $trip1->departureDate = $this->testDataService->getDateTimeImmutable(now()->subDays(10));
        $trip1->returnDate = $this->testDataService->getDateTimeImmutable(now()->subDays(5));
        $trip1->countries = [Country::nld()];

        /** @var Trip $trip2 */
        $trip2 = Trip::getSchema()->getCurrentVersion()->newInstance();
        $trip2->departureDate = $this->testDataService->getDateTimeImmutable(now()->subDays(4));
        $trip2->returnDate = $this->testDataService->getDateTimeImmutable(now()->subDays(2));
        $trip2->countries = [Country::deu()];

        /** @var Abroad $abroad */
        $abroad = Abroad::getSchema()->getCurrentVersion()->newInstance();
        $abroad->wasAbroad = YesNoUnknown::yes();
        $abroad->trips = [$trip1, $trip2];

        $this->caseFragmentService->storeFragment($case->uuid, "abroad", $abroad);

        $this->testDataService->createTasks($case, TaskGroup::contact(), rand(0, 3));

        return $case;
    }

    public function createCaseAbroadAndBack(
        EloquentOrganisation $organisation,
        EloquentUser $planner,
    ): EloquentCase {
        $case = $this->testDataService->createCase($organisation, $planner);

        /** @var Trip $trip */
        $trip = Trip::getSchema()->getCurrentVersion()->newInstance();
        $trip->departureDate = $this->testDataService->getDateTimeImmutable(now()->subDays(10));
        $trip->returnDate = $this->testDataService->getDateTimeImmutable(now()->subDays(5));
        $trip->countries = [Country::nld()];

        /** @var Abroad $abroad */
        $abroad = Abroad::getSchema()->getCurrentVersion()->newInstance();
        $abroad->wasAbroad = YesNoUnknown::yes();
        $abroad->trips = [$trip];

        $this->caseFragmentService->storeFragment($case->uuid, "abroad", $abroad);

        $this->testDataService->createTasks($case, TaskGroup::contact(), rand(0, 3));

        return $case;
    }

    public function createCaseCountryToCountry(
        EloquentOrganisation $organisation,
        EloquentUser $planner,
    ): EloquentCase {
        $case = $this->testDataService->createCase($organisation, $planner);

        /** @var Trip $trip */
        $trip = Trip::getSchema()->getCurrentVersion()->newInstance();
        $trip->departureDate = $this->testDataService->getDateTimeImmutable(now()->subDays(10));
        $trip->returnDate = $this->testDataService->getDateTimeImmutable(now()->subDays(5));
        $trip->countries = [Country::nld(), Country::bel()];

        /** @var Abroad $abroad */
        $abroad = Abroad::getSchema()->getCurrentVersion()->newInstance();
        $abroad->wasAbroad = YesNoUnknown::yes();
        $abroad->trips = [$trip];

        $this->caseFragmentService->storeFragment($case->uuid, "abroad", $abroad);

        $this->testDataService->createTasks($case, TaskGroup::contact(), rand(0, 3));

        return $case;
    }
}
