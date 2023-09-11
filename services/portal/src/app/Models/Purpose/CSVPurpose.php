<?php

declare(strict_types=1);

namespace App\Models\Purpose;

use InvalidArgumentException;

use function array_key_exists;
use function in_array;

class CSVPurpose
{
    public const CSV_SUBPURPOSES = [
        'B&W' => SubPurpose::B_W,
        'Beroepsgroepen' => SubPurpose::OccupationalGroups,
        'Beschrijven epicurve' => SubPurpose::EpiCurve,
        'Capaciteit' => SubPurpose::Capacity,
        'Delay' => SubPurpose::Delay,
        'Doelgroepen' => SubPurpose::TargetAudiences,
        'Doorlooptijd' => SubPurpose::LeadTime,
        'Epi interpretatie' => SubPurpose::InterpretEpiCurve,
        'Interpretatie epicurve' => SubPurpose::InterpretEpiCurve,
        'Epicurve' => SubPurpose::EpiCurve,
        'Escape' => SubPurpose::Escape,
        'Hotspots' => SubPurpose::Hotspots,
        'Impact maatregelen' => SubPurpose::ImpactMeasures,
        'Impact vaccinaties' => SubPurpose::ImpactVacctinations,
        'Impact varianten' => SubPurpose::ImpactVariants,
        'Internationale hotspots' => SubPurpose::InternationalHotspots,
        'Klachten' => SubPurpose::Complaints,
        'Knelpunten' => SubPurpose::Bottlenecks,
        'Koppelen' => SubPurpose::Linking,
        'Koppelen van entiteiten' => SubPurpose::Linking,
        'Leeftijdsgroepen' => SubPurpose::AgeGroups,
        'Locaties' => SubPurpose::Locations,
        'Lokaal verloop' => SubPurpose::LocalCourse,
        'Monitoring' => SubPurpose::Monitoring,
        'Onderzoeksdeelname' => SubPurpose::ResearchContribution,
        'Opvolging' => SubPurpose::Succession,
        'Personeel' => SubPurpose::Staff,
        'R-waarde' => SubPurpose::R_Value,
        'Regionaal risico' => SubPurpose::RegionalRisk,
        'Relatie' => SubPurpose::Relation,
        'RIVM' => SubPurpose::RIVM,
        'Scholen' => SubPurpose::Schools,
        'Terugkoppeling patient' => SubPurpose::PatientFeedback,
        'Thuisbesmettingen' => SubPurpose::DomesticInfection,
        'Tijdigheid' => SubPurpose::Timeliness,
        'By health authorities' => SubPurpose::ByHealthAuthorities,
        'Transmissiepatroon' => SubPurpose::TransmissionPattern,
        'Uitsplitsingen' => SubPurpose::Separations,
        'Veranderingen testbeleid ' => SubPurpose::ChangesTestPolicy,
        'Veranderingen ziekte-impact' => SubPurpose::ChangesDiseaseImpact,
        'Versiebeheer' => SubPurpose::VersionControl,
        'Volledigheid' => SubPurpose::Completeness,
        'Zorg' => SubPurpose::Care,
    ];

    public static function fromCSVString(string $value): ?SubPurpose
    {
        if (in_array($value, ['', 'nvt', '?'], true)) {
            return null;
        }

        if (array_key_exists($value, self::CSV_SUBPURPOSES)) {
            return self::CSV_SUBPURPOSES[$value];
        }

        if (SubPurpose::tryFrom($value) === null) {
            throw new InvalidArgumentException('Invalid subpurpose: ' . $value);
        }

        return SubPurpose::tryFrom($value);
    }
}
