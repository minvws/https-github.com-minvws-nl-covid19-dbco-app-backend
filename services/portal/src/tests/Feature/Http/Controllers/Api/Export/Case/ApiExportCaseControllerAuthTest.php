<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Case;

use App\Models\Eloquent\EloquentCase;
use App\Models\Export\ExportClient;
use App\Models\Purpose\Purpose;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('export')]
#[Group('export-case')]
class ApiExportCaseControllerAuthTest extends FeatureTestCase
{
    private EloquentCase $case;
    private ExportClient $client;
    private ExportPseudoIdHelper $exportPseudoIdHelper;
    private string $pseudoId;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $this->case = $this->createCaseForOrganisation($organisation);
        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$organisation],
        );
        $this->exportPseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $this->pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->case->uuid, $this->client);
    }

    public function testShowAuthenticatedAsClientIsSuccessful(): void
    {
        $response = $this->be($this->client, 'export')->getJson(sprintf('/api/export/cases/%s/', $this->pseudoId));
        $response->assertStatus(200);
    }

    public function testShowWithCorrectSubjectHeaderIsSuccessful(): void
    {
        $subjectDn = sprintf('MAIL=%s,C=NL,CN=%s', $this->faker->email, $this->client->x509_subject_dn_common_name);
        $headers = ['SSL-Client-Subject-DN' => $subjectDn];
        $response = $this->getJson(sprintf('/api/export/cases/%s/', $this->pseudoId), $headers);
        $response->assertStatus(200);
    }

    public static function wrongSubjectHeaderProvider(): array
    {
        return [
            'incorrectCN' => ['MAIL=john@example.org,C=NL,CN=WrongClientCN'],
            'missingCN' => ['MAIL=john@example.org,C=ClientCommonName'],
            'emptySubject' => [''],
        ];
    }

    #[DataProvider('wrongSubjectHeaderProvider')]
    public function testShowWithIncorrectSubjectHeaderResultsIn401(string $dn): void
    {
        $headers = ['SSL-Client-Subject-DN' => $dn];
        $response = $this->getJson(sprintf('/api/export/cases/%s/', $this->pseudoId), $headers);
        $response->assertStatus(401);
    }

    public function testShowAuthenticatedAsUserResultsIn401(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->getJson(sprintf('/api/export/cases/%s/', $this->pseudoId));
        $response->assertStatus(401);
    }

    public function testShowNotAuthenticatedResultsIn401(): void
    {
        $response = $this->getJson(sprintf('/api/export/cases/%s/', $this->pseudoId));
        $response->assertStatus(401);
    }

    public function testShowAuthenticatedWithoutAccessToOrganisationResultsIn403(): void
    {
        $client = $this->createExportClient();
        $pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->case->uuid, $client);
        $response = $this->be($client, 'export')->getJson(sprintf('/api/export/cases/%s/', $pseudoId));
        $response->assertStatus(403);
    }

    public function testShowAuthenticatedWithIncorrectPseudoIdResultsIn400(): void
    {
        // use the pseudoId from a different client
        $client = $this->createExportClient();
        $pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->case->uuid, $client);
        $response = $this->be($this->client, 'export')->getJson(sprintf('/api/export/cases/%s/', $pseudoId));
        $response->assertStatus(400);
    }
}
