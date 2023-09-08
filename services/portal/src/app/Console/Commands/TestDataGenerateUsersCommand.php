<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Closure;
use Illuminate\Console\Command;

use function count;
use function in_array;
use function is_array;
use function max;

/**
 * @codeCoverageIgnore
 */
class TestDataGenerateUsersCommand extends Command
{
    /** @var string $signature */
    protected $signature = 'test-data:generate-users {role} {amountOfUsers} {--organisation=*}';

    /** @var string $description */
    protected $description = 'Creates a set of test users with the given roles for all or the given organisations. The given amount will be used per organisation.';

    public function handle(): int
    {
        $role = $this->argument('role');
        if (!in_array($role, ['user', 'planner'], true)) {
            $this->error('Invalid role');
            return self::INVALID; // invalid
        }

        $amountOfUsers = max(0, (int) $this->argument('amountOfUsers'));

        $organisationUuids = $this->option('organisation');
        if (!is_array($organisationUuids)) {
            return self::INVALID; // not possible
        }

        $organisations = $this->getOrganisations($organisationUuids);

        $bar = $this->output->createProgressBar($amountOfUsers * count($organisations));
        $bar->setRedrawFrequency(1);
        $bar->start();

        $this->createUsersForOrganisations($role, $amountOfUsers, $organisations, static fn () => $bar->advance());

        $bar->finish();

        $this->output->writeln('');

        return self::SUCCESS;
    }

    private function getOrganisations(array $organisationUuids): array
    {
        $query = EloquentOrganisation::query();
        if (count($organisationUuids) > 0) {
            $query->whereIn('uuid', $organisationUuids);
        }

        return $query->get()->all();
    }

    private function createUsersForOrganisations(string $role, int $amountOfUsers, array $organisations, Closure $onCreated): void
    {
        foreach ($organisations as $organisation) {
            $this->createUsersForOrganisation($role, $amountOfUsers, $organisation, $onCreated);
        }
    }

    private function createUsersForOrganisation(string $role, int $amountOfUsers, EloquentOrganisation $organisation, Closure $onCreated): void
    {
        for ($i = 0; $i < $amountOfUsers; $i++) {
            $user = $this->createUserForOrganisation($role, $organisation);
            $onCreated($user);
        }
    }

    private function createUserForOrganisation(string $role, EloquentOrganisation $organisation): EloquentUser
    {
        /** @var EloquentUser $user */
        $user = EloquentUser::factory()->create(['roles' => $role]);
        $user->organisations()->save($organisation);
        return $user;
    }
}
