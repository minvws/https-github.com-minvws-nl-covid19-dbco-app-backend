<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV1UpTo1;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV2Up;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function array_filter;
use function array_map;
use function assert;

class NCOVondaandcomorBuilder extends AbstractMultiValueBuilder
{
    protected function getValues(EloquentCase $case): array
    {
        // only when deceased
        if ($case->deceased->isDeceased !== YesNoUnknown::yes()) {
            return [];
        }

        // only when at an age < 70
        if (
            $case->deceased->deceasedAt === null ||
            $case->index->dateOfBirth === null ||
            $case->deceased->deceasedAt->diff($case->index->dateOfBirth, true)->y >= 70
        ) {
            return [];
        }

        // PHP Stan happy-maker code.
        assert(isset($case->underlying_suffering));
        assert($case->underlying_suffering instanceof UnderlyingSufferingV1UpTo1 ||
            $case->underlying_suffering instanceof UnderlyingSufferingV2Up);
        assert(isset($case->underlying_suffering->hasUnderlyingSuffering));
        assert($case->underlying_suffering->hasUnderlyingSuffering instanceof YesNoUnknown);
        assert(isset($case->underlying_suffering->items));

        // only when there was underlying suffering
        if (
            $case->underlying_suffering->hasUnderlyingSuffering !== YesNoUnknown::yes() ||
            empty($case->underlying_suffering->items)
        ) {
            return [];
        }

        $codes = array_map(
            fn (UnderlyingSuffering $item) => $this->mapUnderlyingSuffering($item, $case),
            $case->underlying_suffering->items,
        );

        return array_filter($codes); // remove nulls
    }

    private function mapUnderlyingSuffering(UnderlyingSuffering $item, EloquentCase $case): ?string
    {
        // @codeCoverageIgnoreStart
        return match ($item) {
            UnderlyingSuffering::bloodDisease() => '11',
            UnderlyingSuffering::cardioVascular() => '3',
            UnderlyingSuffering::chronic() => '9',
            UnderlyingSuffering::diabetes() => '4',
            UnderlyingSuffering::dementiaAlzheimers() => '15',
            UnderlyingSuffering::malignity() => '10',
            UnderlyingSuffering::liver() => '5',
            UnderlyingSuffering::kidney() => '8',
            UnderlyingSuffering::obesitas() => '14',
            UnderlyingSuffering::transplant() => '11',
            UnderlyingSuffering::parkinson() => '16',
            UnderlyingSuffering::immuneDeficiency() => '7',
            UnderlyingSuffering::neurologicNeuromuscuklar() => '6',
            UnderlyingSuffering::downSyndrome() => '17',
            UnderlyingSuffering::immuneDisorders() => '18',
            UnderlyingSuffering::autoimmuneDisease() => '21',
            UnderlyingSuffering::malignantBloodDisease() => '10',
            UnderlyingSuffering::chronicHeartDisease() => '3',
            UnderlyingSuffering::chronicLungDisease() => '9',
            UnderlyingSuffering::diabetesUnstableGlucoselevels() => '4',
            UnderlyingSuffering::hivUntreated() => '19',
            UnderlyingSuffering::solidTumor() => '22',
            UnderlyingSuffering::liverCirrhosis() => '5',
            UnderlyingSuffering::kidneyDialysis() => '8',
            UnderlyingSuffering::morbidObesity() => '14',
            UnderlyingSuffering::organStemcellTransplant() => '20',
            UnderlyingSuffering::sicklecellDisease() => '23',
            UnderlyingSuffering::chronicNervoussystemDisease() => '6',
            default => null
        };
        // @codeCoverageIgnoreEnd
    }
}
