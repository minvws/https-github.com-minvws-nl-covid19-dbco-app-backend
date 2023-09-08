<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CaseList\CaseListCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use App\Scopes\CaseListAuthScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MinVWS\Codable\Codable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\BCOStatus;

use function app;

/**
 * @property string $uuid
 * @property string $name
 * @property string $organisation_uuid
 * @property bool $is_queue
 * @property bool $is_default
 * @property CarbonImmutable $deleted_at
 *
 * @property Collection<int, EloquentCase> $cases
 * @property EloquentOrganisation $organisation
 *
 * @property ?int $assignedCasesCount
 * @property ?int $unassignedCasesCount
 * @property ?int $completedCasesCount
 * @property ?int $archivedCasesCount
 */
class CaseList extends EloquentBaseModel implements Codable, SchemaObject, SchemaProvider, CaseListCommon
{
    use HasSchema;
    use HasFactory;
    use SoftDeletes;
    use CamelCaseAttributes;

    protected $table = 'case_list';
    protected $fillable = ['name', 'is_queue'];
    protected $casts = [
        'is_default' => 'bool',
        'is_queue' => 'bool',
    ];
    public $timestamps = false;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CaseList');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('name'))->setAllowsNull(false);
        $schema->add(BoolType::createField('isDefault'))->setDefaultValue(false);
        $schema->add(BoolType::createField('isQueue'))->setDefaultValue(false);
        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('organisation'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(app()->make(CaseListAuthScope::class));
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(EloquentCase::class, 'assigned_case_list_uuid');
    }

    public function isInUse(): bool
    {
        return
            $this->cases()
            ->whereNotIn('covidcase.bco_status', [BCOStatus::completed(), BCOStatus::archived()])
            ->count() > 0;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->uuid = $this->uuid;
        $container->name = $this->name;
        $container->isDefault = $this->is_default;
        $container->isQueue = $this->is_queue;

        if (isset($this->assignedCasesCount)) {
            $container->assignedCasesCount = $this->assignedCasesCount;
        }

        if (isset($this->unassignedCasesCount)) {
            $container->unassignedCasesCount = $this->unassignedCasesCount;
        }

        if (isset($this->completedCasesCount)) {
            $container->completedCasesCount = $this->completedCasesCount;
        }

        if (isset($this->archivedCasesCount)) {
            $container->archivedCasesCount = $this->archivedCasesCount;
        }
    }

    /**
     * @inheritDoc
     *
     * @param DecodingContainer $container
     * @param CaseList|object|null $object
     *
     * @return CaseList|object
     */
    public static function decode(DecodingContainer $container, ?object $object = null): object
    {
        $caseList = $object ?? new CaseList();

        if ($container->contains('name')) {
            $caseList->name = $container->name->decodeString();
        }

        if ($container->contains('isQueue')) {
            $caseList->is_queue = $container->isQueue->decodeBool();
        } elseif ($object === null) {
            $caseList->is_queue = false;
        }

        return $caseList;
    }

    /**
     * Checks if another case list instance represents the same list.
     */
    public function isEqual(?CaseList $other): bool
    {
        return $other !== null && $other->uuid === $this->uuid;
    }
}
