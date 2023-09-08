<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Config as ConfigHelper;
use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Services\Export\JSONSchemaService;
use Exception;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ValueError;

use function array_map;
use function assert;
use function base_path;
use function implode;
use function is_int;
use function is_string;

/**
 * Command for generating JSON Schemas for the data catalog.
 *
 * @codeCoverageIgnore
 */
class GenerateJSONSchemas extends Command
{
    protected $signature = 'schema:generate-json-schemas {--C|use-compound-schemas=internal}';
    protected $description = 'Generate JSON Schemas for schemas/enums';

    public function __construct(
        private readonly JSONSchemaService $jsonSchemaService,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $jsonOutputPath = ConfigHelper::string('schema.output.json');

        File::deleteDirectory($jsonOutputPath, true);

        $config = new Config(new SchemaLocationResolver(base_path($jsonOutputPath)));

        try {
            $value = $this->option('use-compound-schemas');
            assert(is_string($value));
            $useCompoundSchemas = UseCompoundSchemas::from($value);
            $config->setUseCompoundSchemas($useCompoundSchemas);
        } catch (ValueError) {
            $validValues = array_map(static fn ($c) => $c->value, UseCompoundSchemas::cases());
            $this->error('Invalid value for "use-compound-schemas" option. Valid values are: ' . implode(', ', $validValues));
            return 1;
        }

        $this->info("Generate JSON Schemas...");
        $this->showProgress($this->jsonSchemaService->generateJSONSchemas($config));
        $this->info('Done.');
        return 0;
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

        $bar->finish();
    }
}
