<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Pregnancy;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV2Up;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Psr\Log\LoggerInterface;
use UnhandledMatchError;

use function assert;
use function count;
use function sprintf;

class NCOVondaandcomorV2Builder extends AbstractMultiValueBuilder
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    protected function getValues(EloquentCase $case): array
    {
        if (!$this->wasIndexUnder70WhenCaseCreated($case)) {
            return [];
        }

        $underlyingSuffering = $case->underlying_suffering;
        assert($underlyingSuffering instanceof UnderlyingSufferingV2Up);

        $returnCodes = new Collection();

        $underlyingSufferingItems = new Collection($underlyingSuffering->items);
        $underlyingSufferingItems->map(function (UnderlyingSuffering $item) use ($returnCodes): Collection {
            return $returnCodes->push($this->mapUnderlyingSuffering($item));
        });

        if ($this->isPregnant($case->pregnancy)) {
            $returnCodes->push('1');
        }

        if ($this->hasUnderlyingSufferingOtherItems($underlyingSuffering)) {
            $returnCodes->push('11');
        }

        $returnCodes = $returnCodes
            ->filter()
            ->unique();

        if ($returnCodes->isNotEmpty()) {
            /** @var array<int, string> $returnCodesArray */
            $returnCodesArray = $returnCodes->toArray();
            return $returnCodesArray;
        }

        if ($this->eitherUnderlyingSufferingValueIs($underlyingSuffering, YesNoUnknown::unknown())) {
            return ['13'];
        }

        if ($this->eitherUnderlyingSufferingValueIs($underlyingSuffering, YesNoUnknown::no())) {
            return ['12'];
        }

        if ($this->eitherUnderlyingSufferingValueIs($underlyingSuffering, YesNoUnknown::yes())) {
            return $underlyingSuffering->hasUnderlyingSuffering === YesNoUnknown::no() ? ['12'] : ['13'];
        }

        return [];
    }

    private function wasIndexUnder70WhenCaseCreated(EloquentCase $case): bool
    {
        if ($case->index->dateOfBirth === null) {
            return false;
        }

        return $case->created_at->diffInYears($case->index->dateOfBirth) < 70;
    }

    private function mapUnderlyingSuffering(UnderlyingSuffering $item): ?string
    {
        try {
            return match ($item) {
                UnderlyingSuffering::bloodDisease() => null,
                UnderlyingSuffering::cardioVascular() => '3',
                UnderlyingSuffering::chronicHeartDisease() => '3',
                UnderlyingSuffering::diabetes() => '4',
                UnderlyingSuffering::diabetesUnstableGlucoselevels() => '4',
                UnderlyingSuffering::liver() => '5',
                UnderlyingSuffering::liverCirrhosis() => '5',
                UnderlyingSuffering::chronicNervoussystemDisease() => '6',
                UnderlyingSuffering::neurologicNeuromuscuklar() => '6',
                UnderlyingSuffering::kidney() => '8',
                UnderlyingSuffering::kidneyDialysis() => '8',
                UnderlyingSuffering::chronic() => '9',
                UnderlyingSuffering::chronicLungDisease() => '9',
                UnderlyingSuffering::malignity() => '10',
                UnderlyingSuffering::malignantBloodDisease() => '10',
                UnderlyingSuffering::morbidObesity() => '14',
                UnderlyingSuffering::obesitas() => '14',
                UnderlyingSuffering::dementiaAlzheimers() => '15',
                UnderlyingSuffering::parkinson() => '16',
                UnderlyingSuffering::downSyndrome() => '17',
                UnderlyingSuffering::immuneDeficiency() => '18',
                UnderlyingSuffering::immuneDisorders() => '18',
                UnderlyingSuffering::hivUntreated() => '19',
                UnderlyingSuffering::transplant() => '20',
                UnderlyingSuffering::organStemcellTransplant() => '20',
                UnderlyingSuffering::autoimmuneDisease() => '21',
                UnderlyingSuffering::solidTumor() => '22',
                UnderlyingSuffering::sicklecellDisease() => '23',
            };
        } catch (UnhandledMatchError $unhandledMatchError) {
            $this->logger->warning(sprintf('%s: %s', $unhandledMatchError->getMessage(), $item));

            return null;
        }
    }

    private function isPregnant(Pregnancy $pregnancy): bool
    {
        return $pregnancy->isPregnant === YesNoUnknown::yes();
    }

    private function hasUnderlyingSufferingOtherItems(UnderlyingSufferingV2Up $underlyingSuffering): bool
    {
        return $underlyingSuffering->otherItems !== null && count($underlyingSuffering->otherItems) > 0;
    }

    private function eitherUnderlyingSufferingValueIs(
        UnderlyingSufferingV2Up $underLyingSuffering,
        YesNoUnknown $valueForUnderlyingSuffering,
    ): bool {
        if ($underLyingSuffering->hasUnderlyingSufferingOrMedication === $valueForUnderlyingSuffering) {
            return true;
        }

        return $underLyingSuffering->hasUnderlyingSuffering === $valueForUnderlyingSuffering;
    }
}
