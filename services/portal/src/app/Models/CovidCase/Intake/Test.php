<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property ?DateTimeInterface $dateOfSymptomOnset
 * @property ?DateTimeInterface $dateOfTest
 * @property ?InfectionIndicator $infectionIndicator
 * @property ?YesNoUnknown $isReinfection
 * @property ?DateTimeInterface $previousInfectionDateOfSymptom
 */
class Test extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(DateTimeType::createField('dateOfSymptomOnset', 'Y-m-d'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning('after_or_equal:maxBeforeCaseCreationDate')
            ->addOsirisFinished('required_with:isSymptomOnsetEstimated');
        $schema->add(DateTimeType::createField('dateOfTest', 'Y-m-d'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('before_or_equal:today')
            ->addWarning('after_or_equal:maxBeforeCaseCreationDate');

        $schema->add(InfectionIndicator::getVersion(1)->createField('infectionIndicator'))
            ->getValidationRules()
            ->addOsirisFinished('required');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isReinfection'));
        $schema->add(DateTimeType::createField('previousInfectionDateOfSymptom', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:caseCreationDate')
            ->addWarning('after_or_equal:maxPreviousInfectionDateOfSymptom');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
