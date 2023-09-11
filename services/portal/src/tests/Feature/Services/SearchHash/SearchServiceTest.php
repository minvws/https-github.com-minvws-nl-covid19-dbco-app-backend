<?php

declare(strict_types=1);

namespace Tests\Feature\Services\SearchHash;

use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentUser;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Services\SearchHash\SearchService;
use App\Services\SearchHash\Slot\Slots;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('search-hash')]
class SearchServiceTest extends FeatureTestCase
{
    private SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = $this->app->make(SearchService::class);
    }

    #[DataProvider('getRolesData')]
    public function testSearch(string $roles): void
    {
        $user = $this->createUserWithOrganisationAndLogin($roles);
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $task = $this->createTaskForUser($user, [
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                },
            ),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(1, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(1, $contactResults, 'Contact results count expectation does not match!');

        $this->assertEquals($case->uuid, $indexResults->first()->searchedModel->uuid);
        $this->assertEquals($task->uuid, $contactResults->first()->searchedModel->uuid);
    }

    public function testSearchWithBsn(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'lastThreeBsnDigits' => (string) $this->faker->numberBetween(100, 999),
        ];

        $case = $this->createCaseForOrganisation($organisation, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
                $index->bsnCensored = $valueArray['lastThreeBsnDigits'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $task = $this->createTaskForUser($user, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                    $personalDetails->bsnCensored = $valueArray['lastThreeBsnDigits'];
                },
            ),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(1, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(1, $contactResults, 'Contact results count expectation does not match!');

        $this->assertEquals($case->uuid, $indexResults->first()->searchedModel->uuid);
        $this->assertEquals($task->uuid, $contactResults->first()->searchedModel->uuid);
    }

    public function testSearchWithNonMatchingBsn(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'lastThreeBsnDigits' => '007',
        ];

        $this->createCaseForOrganisation($organisation, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
                $index->bsnCensored = '006';
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $this->createTaskForUser($user, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                    $personalDetails->bsnCensored = '006';
                },
            ),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public function testSearchWithNonProvidedBsn(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $this->createCaseForOrganisation($organisation, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
                $index->bsnCensored = '007';
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $this->createTaskForUser($user, [
            'pseudoBsnGuid' => $this->faker->uuid(),
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                    $personalDetails->bsnCensored = '007';
                },
            ),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public function testSearchNoResults(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->unique()->dateTimeBetween(),
            'lastname' => $this->faker->unique()->lastName(),
            'phone' => $this->faker->unique()->phoneNumber(),
        ];

        $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->unique()->dateTimeBetween();
                $index->lastname = $this->faker->unique()->lastName();
            }),
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->phone = $this->faker->unique()->phoneNumber();
            }),
        ]);

        $this->createTaskForUser($user, [
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->lastname = $this->faker->unique()->lastName();
                $general->phone = $this->faker->unique()->phoneNumber();
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                function (PersonalDetails $personalDetails): void {
                    $personalDetails->dateOfBirth = $this->faker->unique()->dateTimeBetween();
                },
            ),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public function testSearchExpiredCaseResults(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->setTouchedRelations([]);
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        // Save the update quietly as it will update this field with a hook
        $case->updatedAt = CarbonImmutable::now()->subMonths(6);
        $case->saveQuietly();

        $this->createTaskForUser($user, [
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                },
            ),
            'updatedAt' => CarbonImmutable::now()->subDays(29),
        ]);

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public function testSearchWithCasesFromAnotherRegion(): void
    {
        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $this->createCaseForUser($this->createUserWithOrganisation(), [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $this->createTaskForUser($this->createUserWithOrganisation(), [
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                },
            ),
        ]);

        $this->createUserWithOrganisationAndLogin();

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public function testSearchContactWhoseCaseIsDeleted(): void
    {
        $user = $this->createUserWithOrganisationAndLogin();
        $organisation = $user->getOrganisation();

        $valueArray = [
            'dateOfBirth' => $this->faker->dateTimeBetween(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($valueArray): void {
                $index->dateOfBirth = $valueArray['dateOfBirth'];
                $index->lastname = $valueArray['lastname'];
            }),
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($valueArray): void {
                $contact->phone = $valueArray['phone'];
            }),
        ]);

        $this->createTaskForCase($case, [
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($valueArray): void {
                $general->lastname = $valueArray['lastname'];
                $general->phone = $valueArray['phone'];
            }),
            'personalDetails' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($valueArray): void {
                    $personalDetails->dateOfBirth = $valueArray['dateOfBirth'];
                },
            ),
        ]);

        $case->delete();

        $results = $this->searchService->search(new Slots(...$valueArray));

        $indexResults = $results->where('searchHashResultType', SearchHashResultType::index());
        $contactResults = $results->where('searchHashResultType', SearchHashResultType::contact());

        $this->assertCount(0, $indexResults, 'Index results count expectation does not match!');
        $this->assertCount(0, $contactResults, 'Contact results count expectation does not match!');
    }

    public static function getRolesData(): array
    {
        return [
            'Basic Callcenter' => [
                'roles' => 'callcenter',
            ],
            'Expert Callcenter' => [
                'roles' => 'callcenter_expert',
            ],
        ];
    }

    private function createUserWithOrganisationAndLogin(?string $roles = 'callcenter'): EloquentUser
    {
        $user = $this->createUserWithOrganisation(roles: $roles);
        $this->be($user);

        return $user;
    }
}
