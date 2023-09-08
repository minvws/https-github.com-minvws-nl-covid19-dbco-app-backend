<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

/**
 * @property string $uuid
 * @property string $code
 * @property string $label
 * @property bool $is_selectable
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Collection<int, EloquentCase> $cases
 * @property Collection<int, EloquentOrganisation> $organisations
 */
class CaseLabel extends EloquentBaseModel implements Decodable, Encodable
{
    use HasFactory;

    protected $table = 'case_label';
    protected $casts = [
        'is_selectable' => 'boolean',
    ];

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(EloquentCase::class, 'case_case_label', 'case_label_uuid', 'case_uuid');
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(EloquentOrganisation::class, 'case_label_organisation', 'case_label_uuid', 'organisation_uuid');
    }

    /**
     * @inheritDoc
     *
     * Note: only a uuid is expected when decoding!
     */
    public static function decode(DecodingContainer $container, ?object $object = null)
    {
        /** @var static $caseLabel */
        $caseLabel = self::findOrFail($container->decodeString());
        return $caseLabel;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->uuid = $this->uuid;
        $container->label = $this->label;
        $container->is_selectable = $this->is_selectable;
    }
}
