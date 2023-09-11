<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Shared\BaseVaccination;
use App\Models\Versions\Task\Vaccination\VaccinationCommon;
use App\Models\Versions\Task\Vaccination\VaccinationV1UpTo2;
use App\Models\Versions\Task\Vaccination\VaccinationV3Up;
use App\Schema\Schema;
use Illuminate\Support\Collection;

use function app;
use function assert;
use function collect;

class Vaccination extends BaseVaccination implements VaccinationCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = parent::loadSchema();

        $schema->setCurrentVersion(3);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Vaccination');
        $schema->setDocumentationIdentifier('task.vaccination');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function vaccineInjections(): Collection
    {
        assert($this instanceof VaccinationV1UpTo2 || $this instanceof VaccinationV3Up);

        return collect($this->vaccineInjections);
    }
}
