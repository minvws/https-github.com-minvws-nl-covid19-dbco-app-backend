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
use MinVWS\DBCO\Enum\Models\Country;

use function app;

/**
 * @property ?DateTimeInterface $returnDate
 * @property ?DateTimeInterface $departureDate
 * @property ?array<Country> $countries
 */
class Trip extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(DateTimeType::createField('returnDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('after_or_equal:{PREFIX_PATH}departureDate');
        $schema->add(DateTimeType::createField('departureDate', 'Y-m-d'));
        $schema->add(Country::getVersion(1)->createArrayField('countries'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
