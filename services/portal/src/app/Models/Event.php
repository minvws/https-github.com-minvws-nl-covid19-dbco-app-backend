<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Fields\PseudonomizedField;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Scopes\OrganisationAuthScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function app;

/**
 * @property string $uuid
 * @property string $type
 * @property array $data
 * @property array $export_data
 * @property ?string $export_uuid
 * @property CarbonImmutable $created_at
 * @property ?string $organisation_uuid
 *
 * @property ?EloquentOrganisation $organisation
 */
class Event extends EloquentBaseModel implements SchemaObject, SchemaProvider
{
    use HasFactory;
    use HasSchema;
    use CamelCaseAttributes;

    protected $table = 'event';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'type',
        'data',
        'export_data',
        'export_uuid',
        'created_at',
        'case_uuid',
        'organisation_uuid',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'data' => 'json',
        'export_data' => 'json',
    ];

    public static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setUseVersionedClasses(true);
        $schema->setDocumentationIdentifier('event');
        $schema->setVersionedNamespace('App\\Models\\Versions\\Event');
        $schema->setCurrentVersion(1);

        IdFieldsHelper::addIdFieldsToSchema($schema);

        $schema->add(PseudonomizedField::createFromNestedField('pseudoCaseId', 'data', 'caseUuid'));
        $schema->add(PseudonomizedField::createFromNestedField('pseudoTaskId', 'data', 'taskUuid'));

        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('organisation'))->setAllowsNull(false);
        $schema->add(StringType::createField('type'))->setAllowsNull(false);
        $schema->add(StringType::createField('data'))->setAllowsNull(false)->setExcluded();
        $schema->add(StringType::createField('exportData'))->setAllowsNull(false)->setExcluded();
        $schema->add(StringType::createField('exportUuid'))->setAllowsNull(false)->setExcluded();
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function organisation(): BelongsTo
    {
        return
            $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid')
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }
}
