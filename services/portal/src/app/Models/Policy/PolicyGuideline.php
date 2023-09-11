<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Events\PolicyGuidelineCreated;
use Carbon\CarbonInterface;
use Database\Factories\Eloquent\Policy\PolicyGuidelineFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

/**
 * @property string $uuid
 * @property string $identifier
 * @property string $policy_version_uuid
 * @property PolicyPersonType $person_type
 * @property string $name
 * @property PolicyGuidelineReferenceField $source_start_date_reference
 * @property int $source_start_date_addition
 * @property PolicyGuidelineReferenceField $source_end_date_reference
 * @property int $source_end_date_addition
 * @property PolicyGuidelineReferenceField $contagious_start_date_reference
 * @property int $contagious_start_date_addition
 * @property PolicyGuidelineReferenceField $contagious_end_date_reference
 * @property int $contagious_end_date_addition
 * @property ?int $sort_order
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property PolicyVersion $policyVersion
 * @property Collection<int,RiskProfile> $riskProfiles
 * @property Collection<int,CalendarItemConfig> $calendarItemConfigs
 *
 * @method static PolicyGuidelineFactory<static> factory()
 *
 * @phpstan-type PolicyGuidelineAttributes = array{
 *      uuid?: string,
 *      policy_version_uuid?: string,
 *      person_type?: PolicyPersonType,
 *      name?: string,
 *      identifier?: string,
 *      source_start_date_reference?: PolicyGuidelineReferenceField,
 *      source_start_date_addition?: int,
 *      source_end_date_reference?: PolicyGuidelineReferenceField,
 *      source_end_date_addition?: int,
 *      contagious_start_date_reference?: PolicyGuidelineReferenceField,
 *      contagious_start_date_addition?: int,
 *      contagious_end_date_reference?: PolicyGuidelineReferenceField,
 *      contagious_end_date_addition?: int,
 *      sort_order?: int,
 * }
 */
class PolicyGuideline extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'policy_guideline';

    protected $casts = [
        'person_type' => PolicyPersonType::class,
        'source_start_date_reference' => PolicyGuidelineReferenceField::class,
        'source_end_date_reference' => PolicyGuidelineReferenceField::class,
        'contagious_start_date_reference' => PolicyGuidelineReferenceField::class,
        'contagious_end_date_reference' => PolicyGuidelineReferenceField::class,
    ];

    protected $dispatchesEvents = [
        'created' => PolicyGuidelineCreated::class,
    ];

    protected $fillable = [
        'uuid',
        'identifier',
        'policy_version_uuid',
        'person_type',
        'name',
        'source_start_date_reference',
        'source_start_date_addition',
        'source_end_date_reference',
        'source_end_date_addition',
        'contagious_start_date_reference',
        'contagious_start_date_addition',
        'contagious_end_date_reference',
        'contagious_end_date_addition',
        'sort_order',
    ];

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(PolicyVersion::class);
    }

    public function calendarItemConfigs(): HasMany
    {
        return $this->hasMany(CalendarItemConfig::class);
    }

    public function riskProfiles(): HasMany
    {
        return $this->hasMany(RiskProfile::class);
    }

    protected static function newFactory(): PolicyGuidelineFactory
    {
        return new PolicyGuidelineFactory();
    }
}
