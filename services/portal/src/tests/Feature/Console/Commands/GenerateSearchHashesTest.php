<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\Support\Timeout;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\CovidCaseSearch;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\TaskSearch;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Repositories\DbSearchHashCaseRepository;
use App\Repositories\SearchHashCaseRepository;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\CreateFragments;

/**
 * NOTE:
 * The --wipeAllHashes flags uses SearchHashCaseRepository->truncateCovidCaseSearch() to truncate the table.
 * Truncating a table however causes an implicit transaction commit. Using this in combination with
 * DatabaseTransactions test trait will therefore cause an error.
 * See https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html
 */
#[Group('search-hash')]
class GenerateSearchHashesTest extends FeatureTestCase
{
    use CreateFragments;

    public function testGenerateSearchHashes(): void
    {
        $doCreateCase = $this->doCreateCase(...);
        $doCreateTask = $this->doCreateTask(...);

        // phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        EloquentCase::factory()
            ->count(4)
            ->has(
                EloquentTask::factory()->state(fn(): array => $doCreateTask()),
                'tasks',
            )
            ->create(fn (): array => $doCreateCase());
        // phpcs:enable

        CovidCaseSearch::query()->delete();
        TaskSearch::query()->delete();

        $this->artisan('search-hash:generate --chunkSize=2 --sleepPerChunk=1 -v')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '2'],
                ['sleepPerChunk', '1'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', 'NULL'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'yes')
            ->expectsOutput('Generating search hashes for cases...')
            ->expectsOutput('Current page: 1')
            ->expectsOutput('Current page: 2')
            ->doesntExpectOutput('Current page: 3')
            ->assertSuccessful();

        $this->assertTrue(CovidCaseSearch::count() > 0, 'No search hashes were generated for cases');
        $this->assertTrue(TaskSearch::count() === 0, 'No search hashes should exist for tasks (indexes)');
    }

    public function testItWillStopAfterSetTimeLimit(): void
    {
        $doCreateCase = $this->doCreateCase(...);
        $doCreateTask = $this->doCreateTask(...);

        // phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        EloquentCase::factory()
            ->count(4)
            ->has(
                EloquentTask::factory()->state(fn(): array => $doCreateTask()),
                'tasks',
            )
            ->create(fn (): array => $doCreateCase());
        // phpcs:enable

        CovidCaseSearch::query()->delete();
        TaskSearch::query()->delete();

        /** @var Timeout&MockInterface $timeout */
        $timeout = Mockery::mock(Timeout::class);
        $timeout->makePartial();

        $timeout->shouldReceive('timedOut')->andReturnTrue();

        $this->app->instance(Timeout::class, $timeout);

        $this->artisan('search-hash:generate --stopAfterMinutes=5 --chunkSize=2 --sleepPerChunk=')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '2'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', '5'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'yes')
            ->expectsOutput('Generating search hashes for cases...')
            ->expectsOutput('Current page: 1')
            ->expectsOutput('Reached stopAfterMinutes limit!')
            ->doesntExpectOutput('Current page: 2')
            ->assertSuccessful();
    }

    public function testItThatItWillReturnAnNonZeroReturnValueIfItDidNotCompleteAndDidNotGotTriggeredByASetLimit(): void
    {
        /** @var SearchHashCaseRepository&MockInterface $searchHashRepository */
        $searchHashRepository = Mockery::mock($this->app->make(DbSearchHashCaseRepository::class));

        $searchHashRepository->shouldReceive('chunk')->once()->andReturn(false);

        $this->app->instance(SearchHashCaseRepository::class, $searchHashRepository);

        $this->artisan('search-hash:generate --sleepPerChunk=')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '1000'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', 'NULL'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'yes')
            ->expectsOutput('Generating search hashes for cases...')
            ->doesntExpectOutput('Current page: 1')
            ->expectsOutput('Stopping with an failure!')
            ->assertFailed();
    }

    public function testItTruncatesTable(): void
    {
        /** @var SearchHashCaseRepository&MockInterface $searchHashRepository */
        $searchHashRepository = Mockery::mock($this->app->make(DbSearchHashCaseRepository::class));
        $searchHashRepository->makePartial();

        $searchHashRepository->shouldReceive('truncateCovidCaseSearch')->once();

        $this->app->instance(SearchHashCaseRepository::class, $searchHashRepository);

        $this->artisan('search-hash:generate --wipeAllHashes --sleepPerChunk=')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'true'],
                ['chunkSize', '1000'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', 'NULL'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'yes')
            ->expectsOutput('Wiping current hashes...')
            ->expectsOutput('Generating search hashes for cases...')
            ->doesntExpectOutput('Current page: 1')
            ->assertSuccessful();
    }

    public function testItStopsRunningAfterCaseCountReached(): void
    {
        $doCreateCase = $this->doCreateCase(...);

        // phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        EloquentCase::factory()
            ->count(6)
            ->create(fn (): array => $doCreateCase());
        // phpcs:enable

        // NOTE: We can't test --wipeAllHashes and use DatabaseTransactions trait at the same time because a "truncate"
        // causes an implicit (transaction) commit. See https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html
        CovidCaseSearch::query()->delete();

        $this->artisan('search-hash:generate --stopAfterCaseCount=4 --chunkSize=2 --sleepPerChunk= -v')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '2'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', '4'],
                ['stopAfterMinutes', 'NULL'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'yes')
            ->expectsOutput('Generating search hashes for cases...')
            ->expectsOutput('Current page: 1')
            ->expectsOutput('Current page: 2')
            ->doesntExpectOutput('Current page: 3')
            ->expectsOutput('Reached stopAfterCaseCount limit!')
            ->assertSuccessful();
    }

    public function testItWillStopRunningWithIncorrectStopAfterCaseCount(): void
    {
        $this->artisan('search-hash:generate --chunkSize=2 --stopAfterCaseCount=9')
            ->expectsOutput('The stopAfterCaseCount should be an increment of chunkSize: 2!')
            ->expectsOutput('Stopping!')
            ->assertFailed();
    }

    public function testEmptyFlagsWillBeSetToDefaultValue(): void
    {
        $this->artisan('search-hash:generate --chunkSize= --sleepPerChunk= ')
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '1000'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', 'NULL'],
                ['cursor', 'NULL'],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'no')
            ->expectsOutput('Stopping...')
            ->assertSuccessful();
    }

    public function testItDisplaysDecodedCursors(): void
    {
        $this->artisan(
            'search-hash:generate --chunkSize= --sleepPerChunk= --cursor=eyJoZWxsbyI6IndvcmxkIiwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ',
        )
            ->expectsTable(['Option Name', 'Option Value'], [
                ['wipeAllHashes', 'false'],
                ['chunkSize', '1000'],
                ['sleepPerChunk', 'NULL'],
                ['stopAfterCaseCount', 'NULL'],
                ['stopAfterMinutes', 'NULL'],
                [
                    'cursor',
                    <<<'CURSOR'
                    array (
                      'hello' => 'world',
                    )
                    CURSOR,
                ],
                ['queue', '\'default\''],
            ])
            ->expectsConfirmation('Do you want to continue this command with the above options?', 'no')
            ->expectsOutput('Stopping...')
            ->assertSuccessful();
    }

    private function doCreateCase(): array
    {
        return [
            'index' => $this->createLatestEloquentCaseFragmentInstance('index', function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween();
                $index->lastname = $this->faker->lastName();
                $index->address = $this->createLatestEloquentCaseFragmentInstance('index.address', function (IndexAddress $address): void {
                    $address->postalCode = $this->faker->postcode();
                    $address->houseNumber = $this->faker->buildingNumber();
                });
            }),
            'contact' => $this->createLatestEloquentCaseFragmentInstance('contact', function (Contact $contact): void {
                $contact->phone = $this->faker->phoneNumber();
            }),
        ];
    }

    private function doCreateTask(): array
    {
        return [
            'general' => $this->createLatestEloquentTaskFragmentInstance('general', function (General $general): void {
                $general->lastname = $this->faker->lastName();
                $general->phone = $this->faker->phoneNumber();
            }),
            'personalDetails' => $this->createLatestEloquentTaskFragmentInstance(
                'personalDetails',
                function (PersonalDetails $personalDetails): void {
                    $personalDetails->dateOfBirth = $this->faker->dateTimeBetween();
                },
            ),
        ];
    }
}
