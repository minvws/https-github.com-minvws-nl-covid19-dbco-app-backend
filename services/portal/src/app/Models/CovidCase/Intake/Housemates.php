<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property ?YesNoUnknown $hasHouseMates
 * @property ?bool $canStrictlyIsolate
 */
class Housemates extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasHouseMates'));
        $schema->add(BoolType::createField('canStrictlyIsolate'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
