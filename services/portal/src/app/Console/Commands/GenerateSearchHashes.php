<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Support\Timeout;
use App\Jobs\ContactSearchHashJob;
use App\Jobs\IndexSearchHashJob;
use App\Repositories\SearchHashCaseRepository;
use App\Services\SearchHash\Dto\SearchHashCase;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use Symfony\Component\OptionsResolver\OptionConfigurator;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function json_encode;
use function number_format;
use function sprintf;
use function trim;
use function var_export;

/**
 * @phpstan-type RunOptions array{
 *      wipeAllHashes: bool,
 *      chunkSize: int,
 *      sleepPerChunk: int,
 *      stopAfterCaseCount: ?int,
 *      stopAfterMinutes: ?int,
 *      cursor: ?string,
 * }
 */
class GenerateSearchHashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        search-hash:generate
            {--W|wipeAllHashes : Wipe all search hashes}
            {--C|chunkSize= : Chunk size}
            {--S|sleepPerChunk= : Sleep in microseconds after each chunk}
            {--stopAfterCaseCount= : The number of cases after which it should stop processing}
            {--stopAfterMinutes= : The number of minutes after which it should stop processing}
            {--cursor= : A cursor string that will be used to start the chunking}
            {--queue= : The queue (name) to use for dispatching the jobs to}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate search hashes';

    protected bool $triggeredSetLimit = false;
    protected int $casesProcessed = 0;
    protected ?Cursor $lastCursor = null;

    public function __construct(
        private readonly SearchHashCaseRepository $repo,
        private readonly Dispatcher $busDispatcher,
        private readonly Timeout $timeout,
        #[Config('searchhash.queue.connection')]
        private readonly string $connection,
        #[Config('searchhash.queue.queue_name')]
        private readonly string $queueName,
        #[Config('searchhash.queue.delayInSeconds')]
        private readonly int $delay,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $runOptions = $this->getRunOptions([
            'wipeAllHashes' => $this->option('wipeAllHashes'),
            'chunkSize' => $this->option('chunkSize'),
            'sleepPerChunk' => $this->option('sleepPerChunk'),
            'stopAfterCaseCount' => $this->option('stopAfterCaseCount'),
            'stopAfterMinutes' => $this->option('stopAfterMinutes'),
            'cursor' => $this->option('cursor'),
            'queue' => $this->option('queue'),
        ]);

        if ($runOptions === false) {
            $this->newLine();
            $this->error('Stopping!');

            return self::FAILURE;
        }

        $this->displayOptionsTable($runOptions);
        if ($this->input->isInteractive() && !$this->confirm('Do you want to continue this command with the above options?')) {
            $this->info('Stopping...');

            return 0;
        }

        if ($runOptions['wipeAllHashes']) {
            $this->info('Wiping current hashes...');

            $this->repo->truncateCovidCaseSearch();
        }

        $complete = $this
            ->setTimeout($runOptions['stopAfterMinutes'])
            ->process($runOptions);

        $this->newLine();

        if ($complete || $this->triggeredSetLimit) {
            return self::SUCCESS;
        }

        $this->error('Stopping with an failure!');

        return self::FAILURE;
    }

    protected function process(array $runOptions): bool
    {
        $this->info('Generating search hashes for cases...');
        $this->newLine();

        return $this->repo
            ->chunk(
                $runOptions['chunkSize'],
                function (Enumerable $cases, int $page, ?Cursor $cursor) use ($runOptions): bool {
                    /** @var Enumerable<int,SearchHashCase> $cases */

                    if (!is_null($cursor)) {
                        $this->lastCursor = $cursor;
                    }

                    $cases
                        ->reject(static fn (SearchHashCase $case): bool => $case->hasHashes)
                        ->each(function (SearchHashCase $case) use ($runOptions): void {
                            $this->casesProcessed++;

                            $this->busDispatcher
                                ->chain([
                                    new IndexSearchHashJob($case->uuid),
                                    new ContactSearchHashJob($case->uuid),
                                ])
                                ->onConnection($this->connection)
                                ->onQueue($runOptions['queue'])
                                ->delay($this->delay)
                                ->dispatch();
                        });

                        $this->printProgress($page);

                    if (!is_null($runOptions['stopAfterCaseCount']) && $this->casesProcessed >= $runOptions['stopAfterCaseCount']) {
                        $this->info('Reached stopAfterCaseCount limit!');

                        $this->triggeredSetLimit = true;

                        return false;
                    }

                    if ($this->timeout->timedOut()) {
                        $this->info('Reached stopAfterMinutes limit!');

                        $this->triggeredSetLimit = true;

                        return false;
                    }

                    return true;
                },
                usleep: $runOptions['sleepPerChunk'],
                startCursor: $runOptions['cursor'],
            );
    }

    /**
     * @phpstan-return RunOptions|false
     */
    protected function getRunOptions(array $options): array|bool
    {
        $resolver = new OptionsResolver();

        $resolver
            ->define('wipeAllHashes')
            ->default(false)
            ->allowedTypes('bool')
            ->info('Wipe all search hashes.');

        $this->validateAndNormalizeIntOption(
            $resolver
                ->define('chunkSize')
                ->allowedTypes('string', 'null')
                ->info('Chunk size for fetching cases'),
            minInclusive: 1,
            maxInclusive: 10_000,
            default: 1000,
        );

        $this->validateAndNormalizeIntOption(
            $resolver
                ->define('sleepPerChunk')
                ->allowedTypes('string', 'null')
                ->info('How long should it (u)sleep in microseconds after each chunk'),
            minInclusive: 0,
            maxInclusive: 30_000_000,
            default: null,
        );

        $this->validateAndNormalizeIntOption(
            $resolver
                ->define('stopAfterCaseCount')
                ->allowedTypes('string', 'null')
                ->info('The number of cases after which it should stop processing'),
            minInclusive: 1,
            maxInclusive: 10_000_000,
            default: null,
        );

        $this->validateAndNormalizeIntOption(
            $resolver
                ->define('stopAfterMinutes')
                ->allowedTypes('string', 'null')
                ->info('The number of minutes after which it should stop processing'),
            minInclusive: 1,
            maxInclusive: 60 * 24 * 7,
            default: null,
        );

        $resolver
            ->define('cursor')
            ->default(null)
            ->allowedTypes('string', 'null')
            ->info('A cursor string that will be used to start the chunking')
            ->allowedValues(static fn ($value): bool => is_null($value) || !is_null(Cursor::fromEncoded($value)))
            ->normalize(static fn (OptionsResolver $options, ?string $value): ?Cursor => Cursor::fromEncoded($value));

        $resolver
            ->define('queue')
            ->default($this->queueName)
            ->allowedTypes('string', 'null')
            ->info('The queue (name) to use for dispatching the jobs to')
            ->allowedValues(static fn ($value): bool => is_null($value) || (is_string($value) && trim($value) !== ''))
            ->normalize(fn (OptionsResolver $options, ?string $value): string => $value ?? $this->queueName);

        /** @var RunOptions $runOptions */
        $runOptions = $resolver->resolve($options);

        if ($runOptions['stopAfterCaseCount'] % $runOptions['chunkSize'] !== 0) {
            $this->warn(sprintf('The stopAfterCaseCount should be an increment of chunkSize: %s!', $runOptions['chunkSize']));

            return false;
        }

        return $runOptions;
    }

    /**
     * @phpstan-param RunOptions $options
     */
    private function displayOptionsTable(array $options): void
    {
        if (!is_null($options['cursor'])) {
            /** @var Cursor $cursor */
            $cursor = $options['cursor'];

            $options['cursor'] = $cursor->toArray();
            unset($options['cursor']['_pointsToNextItems']);
        }

        $this->table(
            ['Option Name', 'Option Value'],
            Collection::make($options)->map(static fn ($value, $key) => [$key, var_export($value, true)]),
        );
    }

    private function normalizeOption(?string $value): int|string|null
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return null;
            }
        }

        return is_numeric($value)
            ? (int) $value
            : $value;
    }

    private function validateAndNormalizeIntOption(
        OptionConfigurator $optionConfigurator,
        int $minInclusive,
        int $maxInclusive,
        ?int $default,
    ): OptionConfigurator {
        return $optionConfigurator
            ->default($default)
            ->allowedValues(function ($value) use ($minInclusive, $maxInclusive) {
                $value = $this->normalizeOption($value);

                return match (true) {
                    is_null($value) => true,
                    is_int($value) && $value >= $minInclusive && $value <= $maxInclusive => true,
                    default => false,
                };
            })
            ->normalize(fn (OptionsResolver $options, string|null $value): int|string|null => $this->normalizeOption($value) ?? $default);
    }

    private function printProgress(int $page): void
    {
        $this->newLine();
        $this->info(sprintf('Current page: %s', number_format($page, thousands_separator: '.')));
        $this->info(sprintf('Current cursor: %s', $this->lastCursor?->encode() ?? '<No cursor>'));

        $cursorParams = $this->lastCursor?->toArray() ?? [];
        unset($cursorParams['_pointsToNextItems']);

        $this->info(sprintf('Cursor params: %s', json_encode($cursorParams)), 'v');
        $this->info(sprintf('Number of cases processed: %s', number_format($this->casesProcessed, thousands_separator: '.')));
    }

    private function setTimeout(?int $stopAfterMinutes): self
    {
        if (!is_null($stopAfterMinutes)) {
            $this->timeout->setTimeoutInMinutes($stopAfterMinutes);
        }

        return $this;
    }
}
