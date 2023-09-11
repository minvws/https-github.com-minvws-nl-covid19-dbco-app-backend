<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class SourceEnvironmentsBuilder implements Builder
{
    private const MAX_ENVIRONMENTS = 3;

    public function __construct()
    {
    }

    public function build(EloquentCase $case): array
    {
        if ($case instanceof CovidCaseV5Up) {
            return [];
        }

        assert($case instanceof CovidCaseV1UpTo4);

        if ($case->sourceEnvironments->hasLikelySourceEnvironments !== YesNoUnknown::yes()) {
            return [];
        }

        return Utils::collectAnswers(
            $case->sourceEnvironments->likelySourceEnvironments,
            self::MAX_ENVIRONMENTS,
            fn (ContextCategory $category, int $index) => [$this->buildEnvironmentAnswer($category, $index + 1)]
        );
    }

    private function buildEnvironmentAnswer(ContextCategory $category, int $index): Answer
    {
        $value = match ($category) {
            ContextCategory::restaurant() => '116',
            ContextCategory::club() => '117',
            ContextCategory::accomodatieBinnenland() => '118',
            ContextCategory::retail() => '119',
            ContextCategory::evenementVast() => '120',
            ContextCategory::evenementZonder() => '121',
            ContextCategory::zwembad() => '122',
            ContextCategory::horecaOverig() => '123',
            ContextCategory::asielzoekerscentrum() => '138',
            ContextCategory::penitentiaireInstelling() => '139',
            ContextCategory::residentieleJeugdinstelling() => '140',
            ContextCategory::opvangOverig() => '141',
            ContextCategory::kinderOpvang() => '124',
            ContextCategory::basisOnderwijs() => '125',
            ContextCategory::voortgezetOnderwijs() => '126',
            ContextCategory::mbo() => '127',
            ContextCategory::hboUniversiteit() => '128',
            ContextCategory::buitenland() => '134',
            ContextCategory::zeeTransport() => '135',
            ContextCategory::vliegTransport() => '136',
            ContextCategory::transportOverige() => '137',
            ContextCategory::thuis() => '101',
            ContextCategory::bezoek() => '102',
            ContextCategory::groep() => '103',
            ContextCategory::feest() => '104',
            ContextCategory::bruiloft() => '105',
            ContextCategory::uitvaart() => '106',
            ContextCategory::religie() => '129',
            ContextCategory::koor() => '130',
            ContextCategory::studentenverening() => '131',
            ContextCategory::sport() => '132',
            ContextCategory::verenigingOverige() => '133',
            ContextCategory::verpleeghuis() => '107',
            ContextCategory::instelling() => '108',
            ContextCategory::ggzInstelling() => '109',
            ContextCategory::begeleid() => '110',
            ContextCategory::dagopvang() => '111',
            ContextCategory::thuiszorg() => '112',
            ContextCategory::ziekenhuis() => '113',
            ContextCategory::huisarts() => '114',
            ContextCategory::zorgOverig() => '115',
            ContextCategory::omgevingBuiten() => '148',
            ContextCategory::waterBuiten() => '149',
            ContextCategory::dieren() => '150',
            ContextCategory::overig() => '151',
            ContextCategory::vleesverwerkingSlachthuis() => '142',
            ContextCategory::landEnTuinbouw() => '143',
            ContextCategory::bouw() => '144',
            ContextCategory::fabriek() => '145',
            ContextCategory::kantoorOverigeBranche() => '146',
            ContextCategory::overigeAndereWerkplek() => '147',
            default => '152' // onbekend / unknown
        };

        return new Answer('NCOVSetting' . $index, $value);
    }
}
