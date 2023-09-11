<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property ?YesNoUnknown $wasAbroad
 * @property ?array<Trip> $trips
 */
class Abroad extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('wasAbroad'));
        $schema->add(Trip::getSchema()->getVersion(1)->createArrayField('trips'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
