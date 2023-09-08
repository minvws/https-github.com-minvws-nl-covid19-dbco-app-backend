<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Schema;

use function sprintf;

/**
 * Represents an interface that contains the fields that are available as of a certain version until
 * a certain maximum version.
 */
class UpToVersionInterface extends VersionInterface
{
    private int $maxVersion;

    public function __construct(Schema $schema, int $minVersion, int $maxVersion)
    {
        parent::__construct($schema, $minVersion, []);

        $this->maxVersion = $maxVersion;
    }

    /**
     * Returns the maximum schema version for this interface.
     */
    public function getMaxVersion(): int
    {
        return $this->maxVersion;
    }

    public function getShortNamePostfix(): string
    {
        return sprintf("V%dUpTo%d", $this->getMinVersion(), $this->getMaxVersion());
    }
}
