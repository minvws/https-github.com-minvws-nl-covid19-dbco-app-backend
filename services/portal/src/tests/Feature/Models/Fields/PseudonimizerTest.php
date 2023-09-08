<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fields;

use App\Models\Event;
use App\Models\Export\ExportClient;
use App\Models\Fields\Pseudonymizer;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;
use Tests\Feature\FeatureTestCase;

use function app;

class PseudonimizerTest extends FeatureTestCase
{
    private mixed $pseudoIdHelper;
    private ExportClient $client;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pseudoIdHelper = app(ExportPseudoIdHelper::class);
        $this->client = $this->createExportClient();
        $this->event = $this->createEvent();
    }

    public function getEncodingContext(): EncodingContext
    {
        $encoder = new Encoder();
        $context = $encoder->getContext();
        Pseudonymizer::registerInContext(fn(string $id) => $this->pseudoIdHelper->idToPseudoIdForClient($id, $this->client), $context);

        return $context;
    }

    public function testTheCaseUuidIsEncryptedByTheHelper(): void
    {
        $context = $this->getEncodingContext();
        $pseudoId = Pseudonymizer::pseudonimizeForContext($this->event->data['caseUuid'], $context);
        $decoded = $this->pseudoIdHelper->pseudoIdToIdForClient($pseudoId, $this->client);

        self::assertEquals($this->event->data['caseUuid'], $decoded);
    }
}
