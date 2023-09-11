<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\ExtensiveContactTracing;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV1;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2Up;
use MinVWS\DBCO\Enum\Models\BCOType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVBcoTypeBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->extensiveContactTracing));
        assert($case->extensiveContactTracing instanceof ExtensiveContactTracing);

        if ($case->extensiveContactTracing instanceof ExtensiveContactTracingV1) {
            return $case->extensiveContactTracing->receivesExtensiveContactTracing === YesNoUnknown::yes() ? '2' : '4';
        }

        assert($case->extensiveContactTracing instanceof ExtensiveContactTracingV2Up);

        return match ($case->extensiveContactTracing->receivesExtensiveContactTracing) {
            BCOType::standard() => '1',
            BCOType::extensive() => '2',
            BCOType::other() => '3',
            default => '4'
        };
    }
}
