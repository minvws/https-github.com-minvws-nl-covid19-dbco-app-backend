<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Generator\PHPVersionCodeGenerator;
use App\Schema\Generator\TypeScriptVersionCodeGenerator;
use App\Schema\SchemaLinter;
use App\Schema\SchemaProvider;
use App\Services\Export\JSONSchemaService;
use Exception;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

use function assert;
use function base_path;
use function config;
use function count;
use function is_a;
use function is_int;
use function is_string;

/**
 * Command for generating interfaces and subclasses for schema versions to make
 * programmatic usage easier.
 *
 * @codeCoverageIgnore
 */
class GenerateSchemaVersions extends Command
{
    // Output to schema package. Shared packages are located in the root of the Portal container.
    private const TS_OUTPUT_PATH = '/shared/packages/schema/src/generated';

    // PHP output is not yet moved to a different location.
    private const PHP_OUTPUT_PATH = '/src/resources/schemas/php';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:generate-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate interfaces and subclasses for schema versions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly SchemaLinter $schemaLinter,
        private readonly PHPVersionCodeGenerator $phpGenerator,
        private readonly TypeScriptVersionCodeGenerator $tsGenerator,
        private readonly JSONSchemaService $jsonSchemaService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $this->info("Generate PHP/TypeScript schema versions...");

        /** @var array<SchemaProvider> $classes */
        $classes = config('schema.classes');

        // Unset override case version so we will generate all schema's
        config()->set('schema.overrideCaseVersion', null);

        $outputPathTypescript = self::TS_OUTPUT_PATH;
        $outputPathPhp = self::PHP_OUTPUT_PATH;
        $jsonBasePath = config('schema.output.json');
        assert(is_string($jsonBasePath));
        $outputPathJson = base_path($jsonBasePath);

        $tempOutputPath = base_path('resources/schemas/_temp');
        $tempOutputPathPhp = $tempOutputPath . '/php';
        $tempOutputPathTs = $tempOutputPath . '/ts';
        $tempOutputPathJson = $tempOutputPath . '/json';

        if (!$this->lintClasses($classes)) {
            $this->error('Done.');
            return 1;
        }

        File::deleteDirectory($tempOutputPath, true);

        try {
            $this->showProgress($this->generateCode($classes, $tempOutputPathPhp, $tempOutputPathTs));
        } catch (Throwable $e) {
            File::deleteDirectory($tempOutputPath);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        $this->info('Generate JSON schemas...');
        $config = new Config(new SchemaLocationResolver($tempOutputPathJson));
        $config->setUseCompoundSchemas(UseCompoundSchemas::Internal);
        $this->showProgress($this->jsonSchemaService->generateJSONSchemas($config));

        $this->info("Moving generated PHP to '$outputPathPhp'");
        File::deleteDirectory($outputPathPhp, true);
        File::copyDirectory($tempOutputPathPhp, $outputPathPhp);

        $this->info("Moving generated Typescript to '$outputPathTypescript'");
        File::deleteDirectory($outputPathTypescript, true);
        File::copyDirectory($tempOutputPathTs, $outputPathTypescript);

        $this->info("Moving generated JSON schemas to '$outputPathJson'");
        File::deleteDirectory($outputPathJson, true);
        File::copyDirectory($tempOutputPathJson, $outputPathJson);

        $this->info("Deleting all temp files");
        File::deleteDirectory($tempOutputPath);

        $this->info('Done.');
        return 0;
    }

    /**
     * Generate versioned class.
     *
     * @throws Exception
     */
    private function generateVersionsForClass(string $class, string $outputPathPhp, string $outputPathTs): void
    {
        if (!is_a($class, SchemaProvider::class, true)) {
            throw new Exception("$class does not implement " . SchemaProvider::class . " interface!");
        }

        $schema = $class::getSchema();
        if (!$schema->isUsingVersionedClasses()) {
            throw new Exception("$class is not using versioned classes!");
        }

        $this->phpGenerator->generate($schema, $outputPathPhp);
        $this->tsGenerator->generate($schema, $outputPathTs);
    }

    /**
     * @param array<SchemaProvider> $classes
     */
    private function lintClasses(array $classes): bool
    {
        $errors = $this->schemaLinter->lintClasses($classes);
        foreach ($errors as $errorMessage) {
            $this->error($errorMessage);
        }
        return empty($errors);
    }

    private function generateCode(array $classes, string $outputPathPhp, string $outputPathTs): Generator
    {
        foreach ($classes as $i => $class) {
            $this->generateVersionsForClass($class, $outputPathPhp, $outputPathTs);
            yield [$i, count($classes), $class];
        }
    }

    private function showProgress(Generator $generator): void
    {
        $bar = $this->output->createProgressBar();
        $bar->setRedrawFrequency(1);
        $bar->setFormat('%current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('');
        $bar->start();

        foreach ($generator as [$step, $maxSteps, $class]) {
            assert(is_int($step) && is_int($maxSteps));
            $bar->setMessage($class !== null ? ' - ' . $class : '');
            $bar->setMaxSteps($maxSteps);
            $bar->setProgress($step);
        }

        $bar->setMessage('');
        $bar->finish();

        $this->output->writeln('');
    }
}
