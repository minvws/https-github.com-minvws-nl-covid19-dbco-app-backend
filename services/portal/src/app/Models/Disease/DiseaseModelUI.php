<?php

declare(strict_types=1);

namespace App\Models\Disease;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\CachesSchema;
use App\Schema\Enum;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;
use App\Schema\Types\EnumType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Disease\DiseaseModelUIFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $disease_model_id
 * @property int $version
 * @property VersionStatus $status
 * @property string $dossier_schema
 * @property string $contact_schema
 * @property string $event_schema
 * @property ?string $translations
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property DiseaseModel $diseaseModel
 */
class DiseaseModelUI extends Model implements SchemaObject, SchemaProvider
{
    use CamelCaseAttributes;
    use HasFactory;
    use CachesSchema;
    use HasVersionStatus;

    protected $table = 'disease_model_ui';

    protected static function newFactory(): DiseaseModelUIFactory
    {
        return new DiseaseModelUIFactory();
    }

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class, false);
        $schema->add(IntType::createField('id'))
            ->setAllowsNull(false)
            ->setIncludedInDecode(false);
        $schema->add(DiseaseModel::getSchema()->getCurrentVersion()->createField('diseaseModel'))
            ->setAllowsNull(false)
            ->setIncludedInDecode(false)
            ->setIncludedInValidate(false);
        $schema->add(IntType::createField('version'))
            ->setAllowsNull(false)
            ->setIncludedInDecode(false);
        $schema->add(EnumType::createField('status', Enum::forBackedEnum(VersionStatus::class)))
            ->setAllowsNull(false)
            ->setIncludedInDecode(false);
        $schema->add(StringType::createField('dossierSchema'))
            ->setAllowsNull(false)
            ->getValidationRules()->addFatal('required');
        $schema->add(StringType::createField('contactSchema'))
            ->setAllowsNull(false)
            ->getValidationRules()->addFatal('required');
        $schema->add(StringType::createField('eventSchema'))
            ->setAllowsNull(false)
            ->getValidationRules()->addFatal('required');
        $schema->add(StringType::createField('translations'));
        return $schema;
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return self::getSchema()->getCurrentVersion();
    }

    public function diseaseModel(): BelongsTo
    {
        return $this->belongsTo(DiseaseModel::class);
    }

    protected function getHasVersionStatusSiblingsRelation(): HasMany
    {
        return $this->diseaseModel->uis();
    }
}
