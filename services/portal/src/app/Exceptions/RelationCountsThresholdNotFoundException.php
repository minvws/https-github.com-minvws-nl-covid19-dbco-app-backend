<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class RelationCountsThresholdNotFoundException extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("Config key 'relationcounts.log_threshold.$class' missing");
    }
}
