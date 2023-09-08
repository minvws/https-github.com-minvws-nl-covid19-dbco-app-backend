<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Test\TestV1;
use App\Models\Versions\CovidCase\Test\TestV2;
use App\Models\Versions\CovidCase\Test\TestV3Up;
use MinVWS\DBCO\Enum\Models\TestReason;

use function array_intersect;
use function assert;
use function count;

class NCOVwaarschuwBuilder extends AbstractSingleValueBuilder
{
    public static function getIsWarnedReasons(): array
    {
        return [
            TestReason::contactWarnedByGgd(),
            TestReason::contact(),
            TestReason::coronamelder(),
        ];
    }

    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: Test/TestCommon should have a property $reasons even if the enum version differs per version
        assert($case->test instanceof TestV1 || $case->test instanceof TestV2 || $case->test instanceof TestV3Up);

        $reasons = $case->test->reasons;
        if ($reasons === null || count($reasons) === 0) {
            return 'Onb';
        }

        $isWarnedReasons = self::getIsWarnedReasons();
        if (count(array_intersect($reasons, $isWarnedReasons)) > 0) {
            return 'J';
        }

        return 'N';
    }
}
