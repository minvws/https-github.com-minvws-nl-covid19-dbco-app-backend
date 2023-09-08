<?php

declare(strict_types=1);

namespace Tests\Faker\Provider;

use Faker\Provider\Base;

class SchemaVersions extends Base
{
    /**
     * @return non-empty-array<int>
     */
    public function incrementalSchemaVersionRange(): array
    {
        $max = $this->generator->numberBetween(1);
        $min = $this->generator->numberBetween(0, $max);
        return [$min, $max];
    }

    /**
     * @return non-empty-array<int>
     */
    public function identicalSchemaVersionRange(): array
    {
        $version = $this->generator->numberBetween();
        return [$version, $version];
    }
}
