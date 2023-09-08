<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\CovidCase\Intake\Contact\General;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\IntakeContact\IntakeContactCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\Codable\Decoder;

use function app;

/**
 * @property string $uuid
 * @property string $intake_uuid
 * @property CarbonImmutable $received_at
 *
 * @property Intake $intake
 */
class IntakeContact extends EloquentBaseModel implements SchemaObject, SchemaProvider, IntakeContactCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'intake_contact';

    public $timestamps = false;

    protected $casts = [
        'received_at' => 'datetime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\IntakeContact');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('type'))->setAllowsNull(false);
        $schema->add(CaseUpdateContactFragment::getSchema()->getVersion(1)->createArrayField('fragments'));
        $schema->add(DateTimeType::createField('receivedAt'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(Intake::class, 'intake_uuid');
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(IntakeContactFragment::class, 'intake_contact_uuid')->orderBy('name');
    }

    public function getGeneralAttribute(): ?General
    {
        return $this->fragments->where('name', 'general')->map(static function (IntakeContactFragment $intakeContactFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeContactFragment->data);
            return General::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }
}
