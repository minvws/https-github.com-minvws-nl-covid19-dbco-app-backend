<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake\Contact;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;

use function app;

/**
 * @property StringType $reference
 * @property BoolType $isSource
 */
class General extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(BoolType::createField('isSource'));
        $schema->add(StringType::createField('reference'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
