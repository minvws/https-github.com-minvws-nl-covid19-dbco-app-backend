<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use App\Models\Eloquent\Person;
use App\Repositories\PersonRepository;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class PersonDateOfBirthEncrypt extends Command
{
    /** @var string $signature */
    protected $signature = 'migrate:data:person-date-of-birth-encrypt
        {--limit=1000 : The max amount of cases to update per query}
    ';

    /** @var string $description */
    protected $description = 'Encrypt date of birth in person table (if not set yet)';

    public function handle(
        PersonRepository $personRepository,
    ): int {
        $this->info('Encrypting date of birth in person table...');

        $limit = (int) $this->option('limit');
        $persons = $personRepository->getPersonsWithUnencryptedDateOfBirth($limit);

        $persons->map(static function (Person $person): void {
            $person->date_of_birth_encrypted = CarbonImmutable::instance($person->date_of_birth);
            $person->save();
        });

        return Command::SUCCESS;
    }
}
