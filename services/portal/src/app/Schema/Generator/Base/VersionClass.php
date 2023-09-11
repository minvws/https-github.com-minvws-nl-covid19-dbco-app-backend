<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Schema;

/**
 * Represents a version subclass for a schema object.
 */
class VersionClass extends VersionType
{
    private int $version;

    /**
     * @param array<VersionInterface> $interfaces
     */
    public function __construct(Schema $schema, int $version, array $interfaces)
    {
        parent::__construct($schema, $interfaces);

        $this->version = $version;
    }

    protected function getShortNamePostfix(): string
    {
        return "V{$this->version}";
    }
}
