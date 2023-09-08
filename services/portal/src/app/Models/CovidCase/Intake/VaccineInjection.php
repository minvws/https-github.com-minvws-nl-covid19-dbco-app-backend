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
use MinVWS\DBCO\Enum\Models\Vaccine;

use function app;

/**
 * @property ?DateTimeInterface $injectionDate
 * @property ?Vaccine $vaccineType
 */
class VaccineInjection extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(DateTimeType::createField('injectionDate', 'Y-m-d'));
        $schema->add(Vaccine::getVersion(1)->createField('vaccineType'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
