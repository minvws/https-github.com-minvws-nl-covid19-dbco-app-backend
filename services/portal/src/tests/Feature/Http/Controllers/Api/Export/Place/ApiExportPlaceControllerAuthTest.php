<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Place;

use App\Models\Eloquent\Place;
use App\Models\Export\ExportClient;
use App\Models\Purpose\Purpose;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('export')]
#[Group('export-place')]
class ApiExportPlaceControllerAuthTest extends FeatureTestCase
{
    private Place $place;
    private ExportClient $client;
    private ExportPseudoIdHelper $exportPseudoIdHelper;
    private string $pseudoId;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $this->place = $this->createPlaceForOrganisation($organisation);
        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$organisation],
        );
        $this->exportPseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $this->pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->place->uuid, $this->client);
    }

    public function testShowAuthenticatedAsClientIsSuccessful(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(200);
    }

    public function testShowWithCorrectSubjectHeaderIsSuccessful(): void
    {
        $response = $this->getJson(sprintf('/api/export/places/%s/', $this->pseudoId), [
            'SSL-Client-Subject-DN' => sprintf(
                'MAIL=%s,C=NL,CN=%s',
                $this->faker->email,
                $this->client->x509_subject_dn_common_name,
            ),
        ]);
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
        $response = $this->getJson(sprintf('/api/export/places/%s/', $this->pseudoId), $headers);
        $response->assertStatus(401);
    }

    public function testShowAuthenticatedAsUserResultsIn401(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)
            ->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(401);
    }

    public function testShowNotAuthenticatedResultsIn401(): void
    {
        $response = $this->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(401);
    }

    public function testShowAuthenticatedWithoutAccessToOrganisationResultsIn403(): void
    {
        $client = $this->createExportClient();
        $pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->place->uuid, $client);
        $response = $this->be($client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $pseudoId));
        $response->assertStatus(403);
    }

    public function testShowAuthenticatedWithIncorrectPseudoIdResultsIn400(): void
    {
        // use the pseudoId from a different client
        $client = $this->createExportClient();
        $pseudoId = $this->exportPseudoIdHelper->idToPseudoIdForClient($this->place->uuid, $client);
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $pseudoId));
        $response->assertStatus(400);
    }
}
