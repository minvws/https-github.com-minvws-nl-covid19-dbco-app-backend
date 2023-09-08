<?php

declare(strict_types=1);

namespace App\Services\Export\Helpers;

use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Models\Export\ExportClient;
use App\Models\Export\Mutation;
use App\Models\Fields\Pseudonymizer;
use App\Schema\Purpose\PurposeLimitedEncodingContext;
use App\Schema\SchemaObject;
use App\Schema\Types\DateTimeType;
use Illuminate\Support\Collection;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;
use stdClass;

use function array_map;
use function assert;
use function route;

class ExportEncodingHelper
{
    public function __construct(
        private readonly ExportPseudoIdHelper $pseudoIdHelper,
        private readonly SchemaLocationResolver $locationResolver,
    ) {
    }

    public function encodeForClient(SchemaObject $object, ExportClient $client): object
    {
        $context = new PurposeLimitedEncodingContext($client->getPurposeLimitation());
        $context->setMode(EncodingContext::MODE_EXPORT);
        Pseudonymizer::registerInContext(fn(string $id) => $this->pseudoIdHelper->idToPseudoIdForClient($id, $client), $context);
        $context->registerDecorator($object::class, $object->getSchemaVersion()->getEncodableDecorator());
        $encoder = new Encoder($context);
        $data = $encoder->encode($object);
        assert($data instanceof stdClass);
        $data->{'$schema'} = $this->locationResolver->getUrlForSchemaVersion($object->getSchemaVersion());
        return $data;
    }

    /**
     * @param Collection<Mutation> $mutations
     *
     * @return array{items: array<array{pseudoId: string, deletedAt: string}|array{pseudoId: string, mutatedAt: string, path: string}>, cursor: string}
     */
    public function encodeMutationsForClient(Collection $mutations, string $route, string $cursorToken, ExportClient $client): array
    {
        $items = array_map(function ($mutation) use ($route, $client) {
            assert($mutation instanceof Mutation);

            $item = [
                'pseudoId' => $this->pseudoIdHelper->idToPseudoIdForClient($mutation->id, $client),
            ];

            if ($mutation->deletedAt !== null) {
                $item['deletedAt'] = $mutation->deletedAt->format(DateTimeType::FORMAT_DATETIME);
            } else {
                $item['mutatedAt'] = $mutation->updatedAt->format(DateTimeType::FORMAT_DATETIME);
                $item['path'] = route($route, ['pseudoId' => $item['pseudoId']], false);
            }

            return $item;
        }, $mutations->toArray());

        return [
            'items' => $items,
            'cursor' => $cursorToken,
        ];
    }
}
