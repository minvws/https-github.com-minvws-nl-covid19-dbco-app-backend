<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exceptions\PostalCodeValidationException;
use App\Helpers\PostalCodeHelper;
use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_merge;
use function config;
use function count;
use function fclose;
use function fgetcsv;
use function file;
use function fopen;
use function is_array;
use function is_object;

final class ZipcodeImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:zipcodes {--file= : Csv file with absolute file path} {--truncate : Truncate the zipcode table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports GGD zipcodes from file';

    private const INSERTS_INCREMENT = 1000;

    private int $counter = 0;

    private int $failed = 0;

    private ProgressBar $bar;

    private array $failedInserts = [];

    private array $organisationNameExceptionMapper = [
        'Dienst Gezondheid & Jeugd ZHZ' => 'GGD Zuid Holland Zuid',
        'Veiligheids- en Gezondheidsregio Gelderland-Midden' => 'GGD Gelderland-Midden',
        'GGD Noord- en Oost-Gelderland' => 'GGD Noord- en Oost Gelderland',
        'GGD Regio Twente' => 'GGD Twente',
        'GGD Rotterdam-Rijnmond' => 'GGD Rotterdam Rijnmond',
        'GGD Hollands-Noorden' => 'GGD Hollands Noorden',
        'GGD Hollands-Midden' => 'GGD Hollands Midden',
        'GGD Zaanstreek/Waterland' => 'GGD Zaanstreek Waterland',
        'GGD Gooi en Vechtstreek' => 'GGD Gooi- en Vechtstreek',
        'GGD West-Brabant' => 'GGD West Brabant',
        'GGD Brabant-Zuidoost' => 'GGD Brabant Zuidoost',
        'GGD Zuid-Limburg' => 'GGD Zuid Limburg',
    ];

    private array $organisationCache = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('truncate')) {
            if (!$this->confirm('Do you wish to truncate the zipcode table?', true)) {
                return self::FAILURE;
            }

            DB::table('zipcode')->delete();
            $this->line("Truncated zipcode table..");
        }

        $filePath = $this->option('file') ?: __DIR__ . '/' . config('misc.commands.importZipcodes.defaultFile');

        try {
            Assert::string($filePath);
            $fd = fopen($filePath, 'r');
        } catch (ErrorException $ex) {
            return $this->fileNotFound($filePath);
        }

        if ($fd) {
            $lineCount = 0;
            $fileContents = file($filePath);
            if (is_array($fileContents)) {
                $lineCount = count($fileContents);
            }
            $this->bar = $this->output->createProgressBar($lineCount);
            $this->bar->start();

            $this->line("Importing zipcodes..");

            $inserts = [];
            $line = fgetcsv($fd, 100, ';');
            while (is_array($line)) {
                [$zipcode, $organisationName] = $line;

                $mappedOrganisationName = $this->organisationNameExceptionMapper[$organisationName] ?? $organisationName;

                try {
                    $inserts[] = [
                        'zipcode' => PostalCodeHelper::normalizeAndValidate($zipcode),
                        'organisation_uuid' => $this->getOrganisationUuid($mappedOrganisationName),
                    ];
                } catch (MultipleRecordsFoundException $e) {
                    $this->error("Found multiple organisations for organisation name: " . $mappedOrganisationName);
                    $this->failed++;
                } catch (RecordsNotFoundException $e) {
                    $this->error("Organisation not found. Name: " . $mappedOrganisationName);
                    $this->failed++;
                } catch (PostalCodeValidationException $e) {
                    $this->error("Postal code not valid: $zipcode");
                    $this->failed++;
                }

                if (count($inserts) % self::INSERTS_INCREMENT === 0) {
                    $this->dbInsertZipcodes($inserts);
                    $inserts = [];
                }

                $line = fgetcsv($fd, 100, ';');
            }

            $this->dbInsertZipcodes($inserts);
            $this->line("Retry failed inserts: " . count($this->failedInserts));
            foreach ($this->failedInserts as $insert) {
                $this->dbInsertZipcodes([$insert], false);
            }

            $this->bar->finish();
            $this->line("Added $this->counter zipcodes! $this->failed failed! Total: $lineCount");

            fclose($fd);
        }
        return self::SUCCESS;
    }

    private function getOrganisationUuid(string $mappedOrganisationName): string
    {
        if (array_key_exists($mappedOrganisationName, $this->organisationCache)) {
            return $this->organisationCache[$mappedOrganisationName];
        }
        /** @var object{uuid: string} $model */
        $model = DB::table('organisation')->where('name', $mappedOrganisationName)->sole('uuid');
        if (is_object($model)) {
            $this->organisationCache[$mappedOrganisationName] = $model->uuid;
        }

        return $this->organisationCache[$mappedOrganisationName];
    }

    private function dbInsertZipcodes(array $inserts, bool $storeFailed = true): void
    {
        try {
            DB::table('zipcode')->insert($inserts);
            $this->counter += count($inserts);
            $this->bar->advance(count($inserts));
        } catch (QueryException $ex) {
            if ($storeFailed) {
                //Store the failed bulk insert and re-execute them one by one at the end of the script
                $this->error("SQL exception: Failed bulk insert... will retry later!");
                $this->failedInserts = array_merge($this->failedInserts, $inserts);
            } else {
                $this->error("SQL exception! Code: " . $ex->getCode());
                $this->failed++;
            }
        }
    }

    private function fileNotFound(string $filePath): int
    {
        $this->output->writeln("Failed opening file: " . $filePath);

        return self::FAILURE;
    }
}
