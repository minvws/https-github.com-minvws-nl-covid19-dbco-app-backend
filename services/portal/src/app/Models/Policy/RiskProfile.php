<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Casts\RiskProfile as RiskProfileCast;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Policy\RiskProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

/**
 * @property string $uuid
 * @property string $policy_version_uuid
 * @property ?string $policy_guideline_uuid
 * @property string $name
 * @property PolicyPersonType $person_type_enum
 * @property IndexRiskProfile|ContactRiskProfile $risk_profile_enum
 * @property bool $is_active
 * @property int $sort_order
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property PolicyVersion $policyVersion
 * @property ?PolicyGuideline $policyGuideline
 *
 * @method static RiskProfileFactory<static> factory()
 *
 * @phpstan-type RiskProfileAttributes = array{
 *      uuid?: string,
 *      policy_version_uuid?: string,
 *      policy_guideline_uuid?: ?string,
 *      name?: string,
 *      person_type_enum?: PolicyPersonType,
 *      risk_profile_enum?: IndexRiskProfile|ContactRiskProfile,
 *      is_active?: bool,
 *      sort_order?: int,
 *  }
 */
class RiskProfile extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'risk_profile';

    protected $casts = [
        'is_active' => 'boolean',
        'person_type_enum' => PolicyPersonType::class,
        'risk_profile_enum' => RiskProfileCast::class,
    ];

    protected $fillable = [
        'uuid',
        'policy_version_uuid',
        'policy_guideline_uuid',
        'name',
        'person_type_enum',
        'risk_profile_enum',
        'is_active',
        'sort_order',
    ];

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(PolicyVersion::class);
    }

    public function policyGuideline(): BelongsTo
    {
        return $this->belongsTo(PolicyGuideline::class);
    }

    protected static function newFactory(): RiskProfileFactory
    {
        return new RiskProfileFactory();
    }
}
