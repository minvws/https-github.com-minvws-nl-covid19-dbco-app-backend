<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CaseUpdateContact\CaseUpdateContactCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\TaskGroup;

use function app;

/**
 * @property string $uuid
 * @property string $case_update_uuid
 * @property string $contact_group
 * @property ?string $label
 * @property ?string $contact_uuid
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property CaseUpdate $caseUpdate
 * @property Collection<int, CaseUpdateContactFragment> $fragments
 */
class CaseUpdateContact extends EloquentBaseModel implements SchemaObject, SchemaProvider, CaseUpdateContactCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'case_update_contact';

    protected $casts = [
        'contact_group' => TaskGroup::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (self $model): void {
            $model->received_at = $model->caseUpdate->received_at;
        });
    }

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CaseUpdateContact');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(TaskGroup::getVersion(1)->createField('contactGroup'))->setAllowsNull(false);
        $schema->add(StringType::createField('label'))->setAllowsNull(true);
        $schema->add(CaseUpdateContactFragment::getSchema()->getVersion(1)->createArrayField('fragments'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function caseUpdate(): BelongsTo
    {
        return $this->belongsTo(CaseUpdate::class, 'case_update_uuid');
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(CaseUpdateContactFragment::class, 'case_update_contact_uuid')->orderBy('name');
    }
}
