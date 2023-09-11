<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Intake;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;
use function collect;

/**
 * @property ?YesNoUnknown $isVaccinated
 * @property ?array<VaccineInjection> $vaccineInjections
 */
class Vaccination extends Entity implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('isVaccinated'));
        $schema->add(VaccineInjection::getSchema()->getVersion(1)->createArrayField('vaccineInjections'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function vaccineInjections(): Collection
    {
        return collect($this->vaccineInjections);
    }
}
