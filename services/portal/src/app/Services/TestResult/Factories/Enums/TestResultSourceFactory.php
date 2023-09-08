<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Enums;

use App\Dto\TestResultReport\Source;
use LogicException;
use MinVWS\DBCO\Enum\Models\TestResultSource;

use function sprintf;

final class TestResultSourceFactory
{
    public static function fromSource(Source $source): TestResultSource
    {
        if ($source->isCoronit()) {
            return TestResultSource::coronit();
        }

        if ($source->isMeldportaal()) {
            return TestResultSource::meldportaal();
        }

        throw new LogicException(
            sprintf(
                'Failed to instantiate "%s" from "%s" with value "%s"',
                TestResultSource::class,
                Source::class,
                $source->toString(),
            ),
        );
    }
}
