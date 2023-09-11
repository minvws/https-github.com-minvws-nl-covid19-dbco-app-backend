<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fields;

use App\Models\Fields\Pseudonymizer;
use App\Schema\Entity;
use App\Schema\Fields\ArrayField;
use App\Schema\Fields\PseudonomizedField;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use MinVWS\Codable\Encoder;
use Tests\Feature\FeatureTestCase;

use function app;

class PseudonimizedFieldTest extends FeatureTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pseudoIdHelper = app(ExportPseudoIdHelper::class);
        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);
        $schema->add(new ArrayField('data', new StringType()))->setAllowsNull(false)->setExcluded();

        $this->schema = $schema;
    }

    public function testEncode(): void
    {
        $this->schema->add(PseudonomizedField::createFromNestedField('pseudoCaseUuid', 'data', 'caseUuid'));
        /** @var Entity $entity */
        $entity = $this->schema->getCurrentVersion()->newInstance();
        $entity->data = [
            'caseUuid' => '1234-1234-1234-1234',
        ];

        $encoder = new Encoder();
        $client = $this->createExportClient();

        $pseudo = $this->pseudoIdHelper->idToPseudoIdForClient($entity->data['caseUuid'], $client);
        Pseudonymizer::registerInContext(
            fn(string $id) => $this->pseudoIdHelper->idToPseudoIdForClient($id, $client),
            $encoder->getContext(),
        );
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $data = $encoder->encode($entity);

        self::assertEquals($pseudo, $data['pseudoCaseUuid']);
    }

    public function testItReturnsNullForFieldValueGivenInvalidValueRetrievalCallback(): void
    {
        $this->schema->add(new PseudonomizedField('pseudoCaseUuid', static fn() => null));
        /** @var Entity $entity */
        $entity = $this->schema->getCurrentVersion()->newInstance();
        $entity->data = [
            'caseUuid' => '1234-1234-1234-1234',
        ];

        $encoder = new Encoder();
        $client = $this->createExportClient();

        $pseudo = $this->pseudoIdHelper->idToPseudoIdForClient($entity->data['caseUuid'], $client);
        Pseudonymizer::registerInContext(
            fn(string $id) => $this->pseudoIdHelper->idToPseudoIdForClient($id, $client),
            $encoder->getContext(),
        );
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $data = $encoder->encode($entity);

        self::assertNotEquals($pseudo, $data['pseudoCaseUuid']);
        self::assertNull($data['pseudoCaseUuid']);
    }
}
