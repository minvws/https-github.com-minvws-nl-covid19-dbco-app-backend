<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Support\BetterChoice;
use App\Console\Commands\Support\Choice;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportClientPurpose;
use App\Models\Purpose\Purpose;
use App\Repositories\OrganisationRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

use function array_filter;
use function array_map;
use function implode;
use function in_array;
use function random_bytes;
use function sodium_crypto_box_keypair;
use function sprintf;

use const SODIUM_CRYPTO_BOX_NONCEBYTES;

class ManageExportClientsCommand extends Command
{
    use BetterChoice;

    public const ACTION_QUIT = 'Quit';
    public const ACTION_CANCEL = 'Cancel';

    public const ACTION_LIST = 'List clients';
    public const ACTION_ADD = 'Add client';
    public const ACTION_EDIT = 'Edit client';
    public const ACTION_DELETE = 'Delete client';

    public const MAIN_ACTIONS = [
        1 => self::ACTION_LIST,
        2 => self::ACTION_ADD,
        3 => self::ACTION_EDIT,
        4 => self::ACTION_DELETE,
        0 => self::ACTION_QUIT,
    ];
    public const DEFAULT_ACTION = self::ACTION_QUIT;

    protected $signature = 'export-client:manage';
    protected $description = 'Manage export clients';

    private OrganisationRepository $organisationRepository;

    /**
     * @throws Throwable
     */
    public function handle(OrganisationRepository $organisationRepository): int
    {
        $this->organisationRepository = $organisationRepository;

        while (true) {
            $action = $this->choice('Select action', self::MAIN_ACTIONS, self::DEFAULT_ACTION);

            switch ($action) {
                case self::ACTION_LIST:
                    $this->listClients();
                    break;
                case self::ACTION_ADD:
                    $this->addClient();
                    break;
                case self::ACTION_EDIT:
                    $this->editClient();
                    break;
                case self::ACTION_DELETE:
                    $this->deleteClient();
                    break;
                default:
                    return self::SUCCESS;
            }
        }
    }

    private function listClients(): void
    {
        $clients = ExportClient::query()
            ->orderBy('name')
            ->get(['name', 'x509_subject_dn_common_name'])
            ->toArray();
        $this->table(['Name', 'X.509 Common Name'], $clients);
    }

    private function selectClient(string $question): ?ExportClient
    {
        /** @var array<int|string, Choice<ExportClient>|Choice<null>> $choices */
        $choices = [];
        foreach (ExportClient::query()->orderBy('name')->get() as $i => $client) {
            /** @var ExportClient $client */

            $choices[$i + 1] = new Choice($client->name, $client);
        }

        $choices[0] = new Choice(self::ACTION_CANCEL, null);

        return $this->betterSingleChoice($question, $choices);
    }

    private function selectPurposes(ExportClient $client): array
    {
        $choices = [];
        foreach (Purpose::cases() as $i => $purpose) {
            $choices[$i + 1] = new Choice(
                $purpose->getLabel(),
                $purpose,
                $client->purposes->contains(static fn (ExportClientPurpose $p) => $p->purpose === $purpose)
            );
        }

        $choices[0] = new Choice('All', null, !$client->exists);

        $purposes = $this->betterMultipleChoice('Purpose limitation (comma separated)', $choices);

        if (in_array(null, $purposes, true)) {
            $purposes = Purpose::cases();
        }

        $purposes = array_filter($purposes);

        $selected = implode(', ', array_map(static fn (Purpose $p) => $p->getLabel(), $purposes));
        $this->info("<fg=green>Selected:<fg=default> [<fg=yellow>" . $selected . '<fg=default>]</>');

        return $purposes;
    }

    /**
     * @return array<int, EloquentOrganisation>
     */
    private function selectOrganisations(ExportClient $client): array
    {
        $choices = [];
        $eloquentOrganisations = $this->organisationRepository->getAll();
        foreach ($eloquentOrganisations as $i => $organisation) {
            $choices[$i + 1] = new Choice(
                $organisation->name,
                $organisation,
                $client->organisations->contains($organisation),
            );
        }

        $choices[0] = new Choice('All', null, !$client->exists);

        $organisations = $this->betterMultipleChoice('Organisations (comma separated)', $choices);

        if (in_array(null, $organisations, true)) {
            $organisations = EloquentOrganisation::all()->all();
        }

        $organisations = array_filter($organisations);

        $selected = implode(', ', array_map(static fn (EloquentOrganisation $o) => $o->name, $organisations));
        $this->info("<fg=green>Selected:<fg=default> [<fg=yellow>" . $selected . '<fg=default>]</>');

        return $organisations;
    }

    private function clientForm(?ExportClient $client = null): void
    {
        if ($client === null) {
            $client = new ExportClient();
            $client->pseudo_id_key_pair = sodium_crypto_box_keypair();
            $client->pseudo_id_nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        }

        $client->name = $this->ask('Name', $client->name);
        $client->x509_subject_dn_common_name = $this->ask('X.509 Common Name', $client->x509_subject_dn_common_name);

        $purposes = $this->selectPurposes($client);
        $organisations = $this->selectOrganisations($client);

        $confirmed = $this->confirm(
            sprintf(
                'Are you sure you want to %s client "%s"?',
                $client->exists ? 'update' : 'add',
                $client->name,
            ),
            true,
        );

        if (!$confirmed) {
            return;
        }

        DB::transaction(static function () use ($client, $purposes, $organisations): void {
            $client->saveOrFail();

            $client->purposes()->delete();
            foreach ($purposes as $purpose) {
                $clientPurpose = $client->purposes()->make();
                $clientPurpose->purpose = $purpose;
                $clientPurpose->saveOrFail();
            }

            $client->organisations()->detach();
            foreach ($organisations as $organisation) {
                $client->organisations()->attach($organisation);
            }
        });
    }

    /**
     * @throws Throwable
     */
    private function addClient(): void
    {
        $this->clientForm();
    }

    /**
     * @throws Throwable
     */
    private function editClient(): void
    {
        $client = $this->selectClient('Select client to edit');
        if ($client === null) {
            return;
        }

        $this->clientForm($client);
    }

    /**
     * @throws Throwable
     */
    private function deleteClient(): void
    {
        $client = $this->selectClient('Select client to delete');
        if ($client === null) {
            return;
        }

        $confirmed = $this->confirm(sprintf('Are you sure you want to delete client "%s"?', $client->name));
        if (!$confirmed) {
            return;
        }

        $client->deleteOrFail();
    }
}
