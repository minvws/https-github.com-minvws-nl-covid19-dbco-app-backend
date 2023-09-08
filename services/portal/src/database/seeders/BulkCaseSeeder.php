<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Place;
use App\Models\Versions\CovidCase\IndexAddress\IndexAddressV1;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use App\Services\PlaceService;
use Carbon\CarbonImmutable;
use Faker\Generator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;
use Ramsey\Uuid\Uuid;
use Tests\CreatesApplication;
use Tests\ModelCreator;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_push;
use function array_rand;
use function array_shift;
use function count;
use function rand;
use function sprintf;

class BulkCaseSeeder extends Seeder
{
    use ModelCreator;
    use CreatesApplication;
    use InteractsWithAuthentication;

    protected const PLACES_BIN_MAX_SIZE = 10;

    protected Application $app;
    private PlaceService $placeService;
    protected array $placesBin = [];
    protected Generator $faker;

    public function __construct(PlaceService $placeService, Generator $faker)
    {
        $this->app = $this->createApplication();
        $this->placeService = $placeService;
        $this->faker = $faker;
    }

    public function run(int $casesToCreate = 10, int $maxContextsToCreate = 5, int $maxTestResultsToCreate = 1): void
    {
        $organisation = EloquentOrganisation::find('00000000-0000-0000-0000-000000000000');

        if (!$organisation instanceof EloquentOrganisation) {
            return;
        }

        $user = $this->createUserForOrganisation($organisation);
        $this->be($user);

        for ($i = 0; $i < $casesToCreate; $i++) {
            $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-3 weeks'));
            $case = $this->createCaseForUser($user, [
                'case_id' => sprintf("%'.07d", $i),
                'created_at' => $createdAt,
            ]);
            $this->createIndexBaseData($case);
            $this->createVariableContextsForCase($maxContextsToCreate, $case);
            $this->createTestResults($maxTestResultsToCreate, $case);
            $this->createRandomOsirisNotifications($case);
            $this->populateOsirisHistory($case);
            $this->createCallToActions($case, $organisation);
            $case->saveQuietly();
            unset($case, $createdAt);
        }
    }

    private function createVariableContextsForCase(int $maxContextsToCreate, EloquentCase $case): void
    {
        if ($maxContextsToCreate === 0) {
            return;
        }

        $usedPlaces = [];
        $contextsInCase = rand(0, $maxContextsToCreate);
        for ($j = 0; $j < $contextsInCase; $j++) {
            $usedPlaces[] = $this->createContextWithPlace($case, $usedPlaces);
        }
    }

    private function createTestResults(int $maxTestResultsToCreate, EloquentCase $case): void
    {
        if ($maxTestResultsToCreate === 0) {
            return;
        }
        $testResultsInCase = rand(1, $maxTestResultsToCreate);
        for ($j = 0; $j < $testResultsInCase; $j++) {
            $this->createTestResultForCase($case);
        }
    }

    private function newPlace(): Place
    {
        $place = $this->createPlace([
            'location_id' => rand(0, 1) ? Uuid::uuid4() : null,
        ]);
        array_push($this->placesBin, $place);
        if (count($this->placesBin) > self::PLACES_BIN_MAX_SIZE) {
            //FIFO places bin
            array_shift($this->placesBin);
        }

        return $place;
    }

    private function usePlaceFromBin(array $filteredPlacesBin): Place
    {
        if (count($filteredPlacesBin) === 0) {
            return $this->newPlace();
        }
        return $filteredPlacesBin[array_rand($filteredPlacesBin)];
    }

    private function createContextWithPlace(EloquentCase $case, array $usedPlaces): Place
    {
        $place = $this->getPlace($usedPlaces);
        $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);
        $this->placeService->setPlaceOrganisationFromCase($place, $case);

        return $place;
    }

    private function getPlace(array $usedPlaces): Place
    {
        $filteredPlacesBin = array_diff($this->placesBin, $usedPlaces);
        return
            count($filteredPlacesBin) === 0
            || (
                rand(0, 5)
                && //Use existing place for 1 out of 5 contexts
                !(count($usedPlaces) >= self::PLACES_BIN_MAX_SIZE)
            )
         ? $this->newPlace() : $this->usePlaceFromBin($filteredPlacesBin);
    }

    private function createIndexBaseData(EloquentCase $case): void
    {
        $case->index->firstname = $this->faker->firstName();
        $case->index->lastname = $this->faker->lastName();
        $case->contact->phone = $this->faker->phoneNumber;
        $case->contact->email = $this->faker->email;
        $addressFragment = IndexAddress::newInstanceWithVersion(IndexAddress::getSchema()->getCurrentVersion()->getVersion());
        $addressFragment->postalCode = $this->faker->postcode;
        Assert::isInstanceOf($addressFragment, IndexAddressV1::class);
        $case->index->address = $addressFragment;
        $case->index->dateOfBirth = $this->faker->dateTimeBetween('-100 years');
    }

    private function createRandomOsirisNotifications(EloquentCase $case): void
    {
        for ($i = 0; $i < rand(0, 3); $i++) {
            $this->createOsirisNotificationForCase($case, [
                'osiris_status' => $i === 0 ? SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL : SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
                'notified_at' => $case->updatedAt,
                'bco_status' => $case->bcoStatus,
            ]);
        }
    }

    private function createCallToActions(EloquentCase $case, EloquentOrganisation $organisation): void
    {
        for ($i = 0; $i < rand(0, 3); $i++) {
            $callToAction = $this->createCallToAction();
            $resource = $this->createResourceForCallToAction($callToAction);

            $this->createChoreForCaseAndOrganisation($case, $organisation, [
                'owner_resource_type' => $resource->type,
                'owner_resource_id' => $resource->id,
            ]);
        }
    }

    private function populateOsirisHistory(EloquentCase $case): void
    {
        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(warnings: (array) $this->faker->sentences()),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::failed(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(errors: (array) $this->faker->sentences()),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::validation(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(
                errors: (array) $this->faker->sentences(),
                warnings: (array) $this->faker->sentences(),
            ),
        ]);
    }
}
