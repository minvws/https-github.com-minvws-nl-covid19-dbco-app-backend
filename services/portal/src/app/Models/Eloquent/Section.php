<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

use function app;

/**
 * @property string $uuid
 * @property string $label
 * @property string $place_uuid
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Place $place
 * @property Collection $contexts
 */
class Section extends EloquentBaseModel implements SchemaProvider, SchemaObject
{
    use HasFactory;
    use HasSchema;

    protected $table = 'section';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Section');
        $schema->setCurrentVersion(1);

        // Pseudo fields
        IdFieldsHelper::addIdFieldsToSchema($schema);

        $schema->add(StringType::createField('label'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class, 'context_section', 'section_uuid', 'context_uuid');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function indexCount(): int
    {
        return $this->contexts()->distinct()->count();
    }
}
