<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property ?YesNoUnknown $hasLikelySourceEnvironments
 * @property ?array<ContextCategory> $likelySourceEnvironments
 */
class SourceEnvironments extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasLikelySourceEnvironments'));
        $schema->add(ContextCategory::getVersion(1)->createArrayField('likelySourceEnvironments'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
