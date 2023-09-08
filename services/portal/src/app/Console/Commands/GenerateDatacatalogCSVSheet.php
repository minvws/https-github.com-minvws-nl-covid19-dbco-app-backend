<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\SchemaProvider;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

use function app;
use function assert;
use function config;
use function copy;
use function count;
use function fclose;
use function fopen;
use function fputcsv;
use function is_a;
use function is_array;
use function is_resource;
use function is_string;
use function resource_path;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Command for generating interfaces and subclasses for schema versions to make
 * programmatic usage easier.
 */
class GenerateDatacatalogCSVSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:generate-datacatalog-csv {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV file with all the entity fields';

    private const CSV_HEADERS = [
        'Entiteit',
        'Veld',
        'Label',
        'Omschrijving',
        'Datatype',
        'Reden_Privacy_Attentie',
        'Epidemiologische surveillance',
        'Kwaliteit van zorg',
        'Bestuurlijke advisering',
        'Operationele bijsturing',
        'Wetenschappelijk onderzoek',
        'Nader te bepalen',
    ];

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $this->info("Generate datacatalog CSV...");

        $filename = $this->argument('filename') ?? 'datacatalog.csv';
        assert(is_string($filename));

        $classes = config('schema.classes');
        assert(is_array($classes));

        $bar = $this->getProgressBar($classes);
        $storeFilePath = resource_path("data/" . $filename);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'datacatalog');
        assert(is_string($tempFilePath));

        $tempFile = fopen($tempFilePath, 'w');
        assert(is_resource($tempFile));

        $this->writeCsvHeader($tempFile);

        foreach ($classes as $class) {
            $bar->setMessage(' - ' . $class);
            $this->generateLinesForClass($class, $tempFile);
            $bar->advance();
        }

        copy($tempFilePath, $storeFilePath);
        $this->closeAndRemoveFile($tempFile, $tempFilePath);

        return $this->finalize($bar);
    }

    /**
     * @param resource $file
     */
    private function writeCsvHeader($file): void
    {
        fputcsv($file, self::CSV_HEADERS);
    }

    /**
     * Generate CSV lines for class.
     *
     * @param resource $file
     *
     * @throws Exception
     */
    private function generateLinesForClass(string $class, $file): void
    {
        if (!is_a($class, SchemaProvider::class, true)) {
            throw new Exception("$class does not implement " . SchemaProvider::class . " interface!");
        }

        $schema = $class::getSchema();
        if (!$schema->isUsingVersionedClasses()) {
            throw new Exception("$class is not using versioned classes!");
        }

        $schema = app(PurposeSpecificationBinder::class)->bind($schema);
        foreach ($schema->getFields() as $field) {
            fputcsv($file, $field->toExportArray());
        }
    }

    private function getProgressBar(array $classes): ProgressBar
    {
        $bar = $this->output->createProgressBar(count($classes));
        $bar->setRedrawFrequency(1);
        $bar->setFormat('%current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('');
        $bar->start();
        return $bar;
    }

    /**
     * @param resource $tempFile
     */
    private function closeAndRemoveFile($tempFile, string $tempFilePath): void
    {
        fclose($tempFile);
        unlink($tempFilePath);
    }

    /**
     * @return int<0,0> can ONLY return 0, but phpstan needs a "range"
     */
    private function finalize(ProgressBar $bar): int
    {
        $bar->setMessage('');
        $bar->finish();

        $this->output->writeln('');

        $this->info('Done.');
        return 0;
    }
}
