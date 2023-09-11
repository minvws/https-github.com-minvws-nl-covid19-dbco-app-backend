<?php

declare(strict_types=1);

namespace App\Models\Disease;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\CachesSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use Database\Factories\Eloquent\Disease\DiseaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property Collection<int, DiseaseModel> $models
 */
class Disease extends Model implements SchemaObject, SchemaProvider
{
    use HasFactory;
    use CamelCaseAttributes;
    use CachesSchema;

    protected $table = 'disease';
    public $timestamps = false;

    protected static function newFactory(): DiseaseFactory
    {
        return new DiseaseFactory();
    }

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class, false);
        $schema->add(IntType::createField('id'))
            ->setAllowsNull(false)
            ->setIncludedInDecode(false);
        $schema->add(StringType::createField('code'))
            ->setAllowsNull(false)
            ->getValidationRules()->addFatal('required');
        $schema->add(StringType::createField('name'))
            ->setAllowsNull(false)
            ->getValidationRules()->addFatal('required');
        return $schema;
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return self::getSchema()->getCurrentVersion();
    }

    public function models(): HasMany
    {
        return $this->hasMany(DiseaseModel::class);
    }
}
