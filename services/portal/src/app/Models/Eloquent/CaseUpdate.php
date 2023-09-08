<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CaseUpdate\CaseUpdateCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function app;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property string $source
 * @property ?string $pseudo_bsn_guid
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property EloquentCase $case
 * @property Collection<int, CaseUpdateContact> $contacts
 * @property Collection<int, CaseUpdateFragment> $fragments
 */
class CaseUpdate extends EloquentBaseModel implements SchemaObject, SchemaProvider, CaseUpdateCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'case_update';

    protected $casts = [
        'received_at' => 'datetime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CaseUpdate');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('source'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('receivedAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(CaseUpdateFragment::getSchema()->getVersion(1)->createArrayField('fragments'));
        $schema->add(CaseUpdateContact::getSchema()->getVersion(1)->createArrayField('contacts'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(CaseUpdateFragment::class, 'case_update_uuid')->orderBy('name');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CaseUpdateContact::class, 'case_update_uuid')->orderBy('uuid');
    }
}
