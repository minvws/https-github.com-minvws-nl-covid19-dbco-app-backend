<?php

/** @noinspection PhpHierarchyChecksInspection */

declare(strict_types=1);

namespace App\Models\Purpose;

use App\Schema\Purpose\SubPurpose as SubPurposeInterface;

enum SubPurpose: string implements SubPurposeInterface
{
    case AgeGroups = 'ageGroups';
    case B_W = 'b_w';
    case Bottlenecks = 'bottlenecks';
    case Capacity = 'capacity';
    case Care = 'care';
    case ChangesDiseaseImpact = 'changesDiseaseImpact';
    case ChangesTestPolicy = 'changesTestPolicy';
    case Complaints = 'complaints';
    case Completeness = 'completeness';
    case Delay = 'delay';
    case DomesticInfection = 'domesticInfection';
    case EpiCurve = 'epiCurve';
    case Escape = 'escape';
    case Hotspots = 'hotspots';
    case ImpactMeasures = 'impactMeasures';
    case ImpactVacctinations = 'impactVacctinations';
    case ImpactVariants = 'impactVariants';
    case InternationalHotspots = 'internationalHotspots';
    case InterpretEpiCurve = 'interpretEpiCurve';
    case LeadTime = 'leadTime';
    case Linking = 'linking';
    case LocalCourse = 'localCourse';
    case Locations = 'locations';
    case Monitoring = 'monitoring';
    case OccupationalGroups = 'occupationalGroups';
    case PatientFeedback = 'patientFeedback';
    case R_Value = 'rValue';
    case RegionalRisk = 'regionalRisk';
    case Relation = 'relation';
    case ResearchContribution = 'researchContribution';
    case RIVM = 'rivm';
    case Schools = 'schools';
    case Separations = 'separations';
    case Staff = 'staff';
    case Succession = 'succession';
    case TargetAudiences = 'targetAudiences';
    case Timeliness = 'timeliness';
    case TransmissionPattern = 'transmissionPattern';
    case VersionControl = 'versionControl';

    case ByHealthAuthorities = 'byHealthAuthorities';

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::AgeGroups => 'Leeftijdsgroepen',
            self::B_W => 'B&W',
            self::Bottlenecks => 'Knelpunten',
            self::ByHealthAuthorities => 'Doelbinding nog niet bepaald: wordt nog vastgesteld door de GGDen',
            self::Capacity => 'Capaciteit',
            self::Care => 'Zorg',
            self::ChangesDiseaseImpact => 'Veranderingen ziekte-impact',
            self::ChangesTestPolicy => 'Veranderingen testbeleid ',
            self::Complaints => 'Klachten',
            self::Completeness => 'Volledigheid',
            self::Delay => 'Delay',
            self::DomesticInfection => 'Thuisbesmettingen',
            self::EpiCurve => 'Beschrijven epicurve',
            self::Escape => 'Escape',
            self::Hotspots => 'Hotspots',
            self::ImpactMeasures => 'Impact maatregelen',
            self::ImpactVacctinations => 'Impact vaccinaties',
            self::ImpactVariants => 'Impact varianten',
            self::InternationalHotspots => 'Internationale hotspots',
            self::InterpretEpiCurve => 'Interpretatie epicurve',
            self::LeadTime => 'Doorlooptijd',
            self::Linking => 'Koppelen van entiteiten',
            self::LocalCourse => 'Lokaal verloop',
            self::Locations => 'Locaties',
            self::Monitoring => 'Monitoring',
            self::OccupationalGroups => 'Beroepsgroepen',
            self::PatientFeedback => 'Terugkoppeling patient',
            self::R_Value => 'R-waarde',
            self::RegionalRisk => 'Regionaal risico',
            self::Relation => 'Relatie',
            self::ResearchContribution => 'Onderzoeksdeelname',
            self::RIVM => 'RIVM',
            self::Schools => 'Scholen',
            self::Separations => 'Uitsplitsingen',
            self::Staff => 'Personeel',
            self::Succession => 'Opvolging',
            self::TargetAudiences => 'Doelgroepen',
            self::Timeliness => 'Tijdigheid',
            self::TransmissionPattern => 'Transmissiepatroon',
            self::VersionControl => 'Versiebeheer',
        };
    }
}
