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
 * @property YesNoUnknown $hasRecentlyGivenBirth
 * @property DateTimeInterface $birthDate
 */
class RecentBirth extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasRecentlyGivenBirth'));
        $schema->add(DateTimeType::createField('birthDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning('after_or_equal:maxRecentBirthBeforeCaseCreation');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
