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
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property YesNoUnknown $isPregnant
 * @property ?DateTimeInterface $dueDate
 */
class Pregnancy extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('isPregnant'));
        $schema->add(DateTimeType::createField('dueDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:maxDueDateAfterCaseCreation')
            ->addWarning('after_or_equal:maxDueDateBeforeCaseCreation');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
