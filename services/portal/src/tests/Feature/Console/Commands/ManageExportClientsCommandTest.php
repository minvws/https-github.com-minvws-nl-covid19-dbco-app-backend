<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ManageExportClientsCommand;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportClientPurpose;
use App\Models\Purpose\Purpose;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;
use function sprintf;

#[Group('export')]
#[Group('export-client')]
class ManageExportClientsCommandTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createOrganisation(['name' => 'A']);
        $this->createOrganisation(['name' => 'B']);
        $this->createOrganisation(['name' => 'C']);
    }

    public function testQuit(): void
    {
        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();
    }

    public function testListClientsEmpty(): void
    {
        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_LIST, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsTable(['Name', 'X.509 Common Name'], [])
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();
    }

    public function testListClients(): void
    {
        $this->createExportClient(['name' => 'A', 'x509_subject_dn_common_name' => 'AAA']);
        $this->createExportClient(['name' => 'C', 'x509_subject_dn_common_name' => 'CCC']);
        $this->createExportClient(['name' => 'B', 'x509_subject_dn_common_name' => 'BBB']);

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_LIST, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsTable(['Name', 'X.509 Common Name'], [['A', 'AAA'], ['B', 'BBB'], ['C', 'CCC']])
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();
    }

    private function getPurposeChoices(): array
    {
        $choices = [];
        foreach (Purpose::cases() as $i => $purpose) {
            $choices[$i + 1] = $purpose->getLabel();
        }
        $choices[0] = 'All';
        return $choices;
    }

    private function getOrganisationChoices(): array
    {
        $choices = [];

        /** @var EloquentOrganisation $organisation */
        foreach (EloquentOrganisation::all() as $i => $organisation) {
            $choices[$i + 1] = $organisation->name;
        }
        $choices[0] = 'All';
        return $choices;
    }

    public static function addAndEditProvider(): Generator
    {
        yield 'all purposes / all organisations' =>
            ['A', 'AAA', ['All'], Purpose::cases(), ['All'], ['A', 'B', 'C']];

        yield 'single purpose' =>
            ['B', 'BBB', [Purpose::cases()[0]->getLabel()], [Purpose::cases()[0]], ['All'], ['A', 'B', 'C']];

        yield 'multiple purposes' => [
            'C',
            'CCC',
            [Purpose::cases()[0]->getLabel(), Purpose::cases()[1]->getLabel()],
            [Purpose::cases()[0], Purpose::cases()[1]],
            ['All'],
            ['A', 'B', 'C'],
        ];

        yield 'single organisation' =>
            ['D', 'DDD', ['All'], Purpose::cases(), ['A'], ['A']];

        yield 'multiple organisations' =>
            ['E', 'EEE', ['All'], Purpose::cases(), ['A', 'B'], ['A', 'B']];
    }

    #[DataProvider('addAndEditProvider')]
    public function testAddClient(
        string $name,
        string $commonName,
        array $selectedPurposes,
        array $expectedPurposes,
        array $selectedOrganisations,
        array $expectedOrganisations,
    ): void {
        $this->assertDatabaseMissing('export_client', ['name' => $name]);

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_ADD, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsQuestion('Name', $name)
            ->expectsQuestion('X.509 Common Name', $commonName)
            ->expectsChoice('Purpose limitation (comma separated)', $selectedPurposes, $this->getPurposeChoices())
            ->expectsChoice('Organisations (comma separated)', $selectedOrganisations, $this->getOrganisationChoices())
            ->expectsConfirmation(sprintf('Are you sure you want to add client "%s"?', $name), 'yes')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        /** @var ExportClient $exportClient */
        $exportClient = ExportClient::query()->firstWhere('name', '=', $name);

        $this->assertEquals($commonName, $exportClient->x509_subject_dn_common_name);

        $this->assertEquals(count($expectedPurposes), count($exportClient->purposes));
        foreach ($expectedPurposes as $purpose) {
            $this->assertTrue(
                $exportClient->purposes->contains(static fn (ExportClientPurpose $cp) => $cp->purpose === $purpose)
            );
        }

        foreach ($expectedOrganisations as $organisation) {
            $this->assertTrue(
                $exportClient->organisations->contains(static fn (EloquentOrganisation $o) => $o->name === $organisation)
            );
        }
    }

    public function testAddClientDontConfirm(): void
    {
        $this->assertEquals(0, ExportClient::query()->count());

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_ADD, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsQuestion('Name', 'A')
            ->expectsQuestion('X.509 Common Name', 'AAAA')
            ->expectsChoice('Purpose limitation (comma separated)', ['All'], $this->getPurposeChoices())
            ->expectsChoice('Organisations (comma separated)', ['All'], $this->getOrganisationChoices())
            ->expectsConfirmation('Are you sure you want to add client "A"?', 'no')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $this->assertEquals(0, ExportClient::query()->count());
    }

    #[DataProvider('addAndEditProvider')]
    public function testEditClient(
        string $name,
        string $commonName,
        array $selectedPurposes,
        array $expectedPurposes,
        array $selectedOrganisations,
        array $expectedOrganisations,
    ): void {
        $client = $this->createExportClient();

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_EDIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsChoice(
                'Select client to edit',
                $client->name,
                [1 => $client->name, 0 => ManageExportClientsCommand::ACTION_CANCEL],
            )
            ->expectsQuestion('Name', $name)
            ->expectsQuestion('X.509 Common Name', $commonName)
            ->expectsChoice('Purpose limitation (comma separated)', $selectedPurposes, $this->getPurposeChoices())
            ->expectsChoice('Organisations (comma separated)', $selectedOrganisations, $this->getOrganisationChoices())
            ->expectsConfirmation(sprintf('Are you sure you want to update client "%s"?', $name), 'yes')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $client->refresh();

        $this->assertEquals($name, $client->name);
        $this->assertEquals($commonName, $client->x509_subject_dn_common_name);

        $this->assertEquals(count($expectedPurposes), count($client->purposes));
        foreach ($expectedPurposes as $purpose) {
            $this->assertTrue(
                $client->purposes->contains(static fn (ExportClientPurpose $cp) => $cp->purpose === $purpose)
            );
        }

        foreach ($expectedOrganisations as $organisation) {
            $this->assertTrue(
                $client->organisations->contains(static fn (EloquentOrganisation $o) => $o->name === $organisation)
            );
        }
    }

    #[DataProvider('addAndEditProvider')]
    public function testEditClientDontConfirm(): void
    {
        $client = $this->createExportClient(
            purposes: [Purpose::cases()[0]],
            organisations: [EloquentOrganisation::query()->first()],
        );
        $client->load(['purposes', 'organisations']);
        $pre = $client->toArray();

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_EDIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsChoice(
                'Select client to edit',
                $client->name,
                [1 => $client->name, 0 => ManageExportClientsCommand::ACTION_CANCEL],
            )
            ->expectsQuestion('Name', 'A')
            ->expectsQuestion('X.509 Common Name', 'AAA')
            ->expectsChoice('Purpose limitation (comma separated)', ['All'], $this->getPurposeChoices())
            ->expectsChoice('Organisations (comma separated)', ['All'], $this->getOrganisationChoices())
            ->expectsConfirmation('Are you sure you want to update client "A"?', 'no')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $client->refresh();
        $client->load(['purposes', 'organisations']);
        $post = $client->toArray();

        $this->assertEquals($pre, $post);
    }

    public function testCancelEditClient(): void
    {
        $client = $this->createExportClient(
            purposes: [Purpose::cases()[0]],
            organisations: [EloquentOrganisation::query()->first()],
        );
        $client->load(['purposes', 'organisations']);
        $pre = $client->toArray();

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_EDIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsChoice(
                'Select client to edit',
                ManageExportClientsCommand::ACTION_CANCEL,
                [1 => $client->name, 0 => ManageExportClientsCommand::ACTION_CANCEL],
            )
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $client->refresh();
        $client->load(['purposes', 'organisations']);
        $post = $client->toArray();

        $this->assertEquals($pre, $post);
    }

    public static function deleteProvider(): Generator
    {
        yield 'yes' => ['yes', 0];
        yield 'no' => ['no', 1];
    }

    #[DataProvider('deleteProvider')]
    public function testDeleteClient(string $answer, int $expectedClients): void
    {
        $client = $this->createExportClient();
        $this->assertEquals(1, ExportClient::query()->count());

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_DELETE, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsChoice('Select client to delete', $client->name, [1 => $client->name, 0 => ManageExportClientsCommand::ACTION_CANCEL])
            ->expectsConfirmation('Are you sure you want to delete client "' . $client->name . '"?', $answer)
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $this->assertEquals($expectedClients, ExportClient::query()->count());
    }

    public function testCancelDeleteClient(): void
    {
        $client = $this->createExportClient();
        $this->assertEquals(1, ExportClient::query()->count());

        $this->artisan('export-client:manage')
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_DELETE, ManageExportClientsCommand::MAIN_ACTIONS)
            ->expectsChoice(
                'Select client to delete',
                ManageExportClientsCommand::ACTION_CANCEL,
                [1 => $client->name, 0 => ManageExportClientsCommand::ACTION_CANCEL],
            )
            ->expectsChoice('Select action', ManageExportClientsCommand::ACTION_QUIT, ManageExportClientsCommand::MAIN_ACTIONS)
            ->assertExitCode(0)
            ->execute();

        $this->assertEquals(1, ExportClient::query()->count());
    }
}
