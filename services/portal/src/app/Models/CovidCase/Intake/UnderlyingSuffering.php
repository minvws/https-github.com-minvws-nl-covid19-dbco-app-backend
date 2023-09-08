<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering as UnderlyingSufferingEnum;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property YesNoUnknown $hasUnderlyingSufferingOrMedication
 * @property YesNoUnknown $hasUnderlyingSuffering
 * @property array<UnderlyingSufferingEnum> $items
 */
class UnderlyingSuffering extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setUseVersionedClasses(true);

        $schema->setCurrentVersion(2);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasUnderlyingSufferingOrMedication'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasUnderlyingSuffering'));

        // Fields up to version 1
        $schema->add(UnderlyingSufferingEnum::getVersion(1)->createArrayField('items'))->setMaxVersion(1);


        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
