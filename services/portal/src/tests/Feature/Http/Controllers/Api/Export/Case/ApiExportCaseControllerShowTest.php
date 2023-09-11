<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Case;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Export\ExportClient;
use App\Models\Purpose\Purpose;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\Relationship;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\MocksEncryptionHelper;

#[Group('export')]
#[Group('export-case')]
class ApiExportCaseControllerShowTest extends FeatureTestCase
{
    use MocksEncryptionHelper;

    private EloquentCase $case;
    private ExportClient $client;
    private string $pseudoId;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $stamp = CarbonImmutable::parse('1 minute ago');
        $this->case = $this->createCaseForOrganisation($organisation, ['created_at' => $stamp, 'updated_at' => $stamp]);
        $this->client = $this->createExportClient(
            purposes: Purpose::cases(),
            organisations: [$organisation],
        );
        $encryptionHelper = $this->app->get(ExportPseudoIdHelper::class);
        $this->pseudoId = $encryptionHelper->idToPseudoIdForClient($this->case->uuid, $this->client);
    }

    public function testShowReturnsExpectedCaseData(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/' . $this->pseudoId . '/');
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('$schema'));
        $this->assertEquals($this->pseudoId, $response->json('pseudoId'));
    }

    public function testShowReturnsEmptyCaseDataIfClientHasNoPurposes(): void
    {
        foreach ($this->client->purposes as $purpose) {
            $purpose->delete();
        }

        $this->client->refresh();

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/' . $this->pseudoId . '/');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('$schema', $data);
        $this->assertNotEmpty($data['$schema']);
    }

    public function testShowResultsIn404IfCaseIsDeleted(): void
    {
        $this->case->delete();
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/' . $this->pseudoId . '/');
        $response->assertStatus(404);
    }

    public function testShowReturnsPartialNullTaskDataWhenEncryptedDataExpired(): void
    {
        // travel back more than the short storage term's active interval so that things expire when we return to the present
        $this->travel(-StorageTerm::short()->getActiveInterval() - 1)->days();

        $questionnaire = EloquentQuestionnaire::factory()->create();

        $question = $this->createQuestionForQuestionnaire($questionnaire, [
            'identifier' => 'contactdetails',
            'group_name' => 'contactdetails',
            'question_type' => 'contactdetails',
        ]);

        $task = $this->createTaskForCase($this->case, ['questionnaire_uuid' => $questionnaire->uuid]);
        $task->general->firstname = null;
        $task->general->lastname = null;
        $task->general->relationship = Relationship::partner();
        $task->save();

        $this->createAnswerForTaskWithQuestion($task, $question, [
            'ctd_firstname' => 'John',
            'ctd_lastname' => 'Doe',
            'ctd_email' => 'john@example.org',
            'ctd_phonenumber' => '555-1234',
        ]);

        $this->case->refresh();
        $this->assertEquals('John', $this->case->tasks[0]->general->firstname);

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/' . $this->pseudoId . '/');
        $response->assertStatus(200);
        $this->assertEquals($this->pseudoId, $response->json('pseudoId'));
        $this->assertEquals(Relationship::partner()->value, $response->json('tasks.0.general.relationship'));

        $this->travelBack();

        $this->case->refresh();
        $this->assertEquals(null, $this->case->tasks[0]->general->firstname);

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/' . $this->pseudoId . '/');
        $response->assertStatus(200);
        $this->assertEquals($this->pseudoId, $response->json('pseudoId'));
        $this->assertEquals(null, $response->json('tasks.0.general.relationship'));
    }
}
