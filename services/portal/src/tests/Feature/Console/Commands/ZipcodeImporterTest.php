<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Eloquent\EloquentOrganisation;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function config;

#[Group('zipcode-importer')]
final class ZipcodeImporterTest extends FeatureTestCase
{
    public function testImportingDefaultFile(): void
    {
        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes')
            ->expectsOutput('Importing zipcodes..')
            ->expectsOutput('Added 3493 zipcodes! 2 failed! Total: 3495')
            ->assertExitCode(0);
    }

    public function testImportingFileOption(): void
    {
        $this->artisan('import:zipcodes --file=/src/app/Console/Commands/data/PC6-GGDregio-stripped-4.csv')
            ->expectsOutput('Importing zipcodes..')
            ->expectsOutput('Added 3493 zipcodes! 2 failed! Total: 3495')
            ->assertExitCode(0)
            ->execute();
    }

    public function testImportingFailedOpeningFile(): void
    {
        $this->artisan('import:zipcodes --file=notfound.csv')
            ->expectsOutput('Failed opening file: notfound.csv')
            ->assertExitCode(1)
            ->execute();
    }

    #[Group('zipcode-truncate')]
    public function testImportingWithTruncate(): void
    {
        $friesland = EloquentOrganisation::where('name', 'GGD Fryslân')->first();
        $this->createZipcode(['zipcode' => '8388MD', 'organisation_uuid' => $friesland->uuid]);

        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes --truncate')
            ->expectsConfirmation('Do you wish to truncate the zipcode table?', 'yes')
            ->expectsOutput('Truncated zipcode table..')
            ->expectsOutput('Importing zipcodes..')
            ->assertExitCode(0);
    }

    #[Group('zipcode-truncate')]
    public function testImportingWithTruncateAndCancel(): void
    {
        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes --truncate')
            ->expectsConfirmation('Do you wish to truncate the zipcode table?', 'no')
            ->assertExitCode(1);
    }

    #[Group('zipcode-duplicate')]
    public function testImportingWithExistingZipcodeAndUnknownOrganisation(): void
    {
        $friesland = EloquentOrganisation::where('name', 'GGD Fryslân')->first();
        $this->createZipcode(['zipcode' => '8388MD', 'organisation_uuid' => $friesland->uuid]);

        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes')
            ->expectsOutput('Importing zipcodes..')
            ->expectsOutput('Organisation not found. Name: Not Found')
            ->expectsOutput("SQL exception! Code: 23000")
            ->expectsOutput('Added 3492 zipcodes! 3 failed! Total: 3495')
            ->assertExitCode(0);
    }

    public function testImportingInvalidPostalCodeShouldFail(): void
    {
        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes')
            ->expectsOutput('Importing zipcodes..')
            ->expectsOutput('Postal code not valid: XL9999')
            ->expectsOutput('Added 3493 zipcodes! 2 failed! Total: 3495')
            ->assertExitCode(0);
    }

    public function testRetrievingMultipleOrganisationsShouldGiveErrorMessage(): void
    {
        $this->createOrganisation(['name' => 'GGD Fryslân']);

        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes')
            ->expectsOutput('Importing zipcodes..')
            ->expectsOutput('Found multiple organisations for organisation name: GGD Fryslân')
            ->assertFailed()
            ->assertExitCode(0);
    }

    public function testImportingUnknownOrganisationsShouldFail(): void
    {
        config()->set('misc.commands.importZipcodes.defaultFile', 'data/PC6-GGDregio-stripped-4.csv');

        $this->artisan('import:zipcodes')
            ->assertFailed()
            ->expectsOutput('Importing zipcodes..')
            ->assertExitCode(0);
    }
}
