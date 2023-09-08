<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\IntakeContactFragment\IntakeContactFragmentCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\AnyType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\SealedJson;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function app;

/**
 * @property string $intake_contact_uuid
 * @property string $name
 * @property array $data
 * @property int $version
 * @property CarbonImmutable $received_at
 *
 * @property IntakeContact $intakeContact
 */
class IntakeContactFragment extends EloquentBaseModel implements SchemaObject, SchemaProvider, IntakeContactFragmentCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'intake_contact_fragment';
    protected $primaryKey = ['intake_contact_uuid', 'name'];
    public $timestamps = false;

    protected $casts = [
        'data' => SealedJson::class . ':' . StorageTerm::SHORT . ',received_at,' . SealedJson::DECODE_OBJECTS_AS_ASSOCIATIVE_ARRAY,
        'received_at' => 'datime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\IntakeContactFragment');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(StringType::createField('name'))->setAllowsNull(false);
        $schema->add(AnyType::createField('data', 'array'))->setAllowsNull(false);
        $schema->add(IntType::createField('version'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('receivedAt'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function intakeContact(): BelongsTo
    {
        return $this->belongsTo(IntakeContact::class, 'intake_contact_uuid');
    }
}
