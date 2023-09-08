<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use function sprintf;

/**
 * Represents an interface that contains the fields that are available as of a certain version until,
 * and including, the latest version.
 */
class UpVersionInterface extends VersionInterface
{
    public function getShortNamePostfix(): string
    {
        if ($this->getMinVersion() === 1) {
            return 'Common';
        }

        return sprintf("V%dUp", $this->getMinVersion());
    }
}
