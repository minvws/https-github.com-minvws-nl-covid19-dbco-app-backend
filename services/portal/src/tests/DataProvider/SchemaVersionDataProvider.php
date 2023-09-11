<?php

declare(strict_types=1);

namespace Tests\DataProvider;

use App\Schema\SchemaProvider;
use Webmozart\Assert\Assert;

use function sprintf;

class SchemaVersionDataProvider
{
    /**
     * @param class-string<SchemaProvider> $schemaProvider
     *
     * @return array<string, array<int>>
     */
    public static function all(string $schemaProvider): array
    {
        Assert::isAOf($schemaProvider, SchemaProvider::class);

        $schema = $schemaProvider::getSchema();
        $schemaName = $schema->getName();

        $dataProvider = [];
        foreach ($schema->getVersions() as $schemaVersion) {
            $dataProvider[sprintf('%s v%s', $schemaName, $schemaVersion)] = [$schemaVersion];
        }

        return $dataProvider;
    }
}
