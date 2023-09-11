<?php

/** @noinspection PhpHierarchyChecksInspection */

declare(strict_types=1);

namespace App\Models\Purpose;

use App\Schema\Purpose\Purpose as PurposeInterface;

/**
 * @implements PurposeInterface<SubPurpose>
 */
enum Purpose: string implements PurposeInterface
{
    case EpidemiologicalSurveillance = 'epidemiologicalSurveillance';
    case QualityOfCare = 'qualityOfCare';
    case AdministrativeAdvice = 'administrativeAdvice';
    case OperationalAdjustment = 'operationalAdjustment';
    case ScientificResearch = 'scientificResearch';

    case ToBeDetermined = 'toBeDetermined';

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::EpidemiologicalSurveillance => 'Epidemiologische surveillance',
            self::QualityOfCare => 'Kwaliteit van zorg',
            self::AdministrativeAdvice => 'Bestuurlijke advisering',
            self::OperationalAdjustment => 'Operationele bijsturing',
            self::ScientificResearch => 'Wetenschappelijk onderzoek',
            self::ToBeDetermined => 'Nader te bepalen',
        };
    }
}
