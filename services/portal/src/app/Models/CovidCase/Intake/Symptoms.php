<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * @property YesNoUnknown $hasSymptoms
 * @property array<Symptom> $symptoms
 */
class Symptoms extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasSymptoms'));
        $schema->add(Symptom::getVersion(1)->createArrayField('symptoms'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
