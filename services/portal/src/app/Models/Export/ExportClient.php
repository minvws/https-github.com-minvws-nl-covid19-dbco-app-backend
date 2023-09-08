<?php

declare(strict_types=1);

namespace App\Models\Export;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\Purpose\PurposeLimitation;
use App\Schema\Purpose\PurposeLimitationBuilder;
use App\Scopes\OrganisationAuthScope;
use Database\Factories\Eloquent\ExportClientFactory;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use MinVWS\DBCO\Encryption\Security\SealedWithKey;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use RuntimeException;

use function assert;

/**
 * @property int $id
 * @property string $name
 * @property string $x509_subject_dn_common_name
 * @property string $pseudo_id_key_pair
 * @property string $pseudo_id_nonce
 *
 * @property Collection<EloquentOrganisation> $organisations
 * @property Collection<ExportClientPurpose> $purposes
 */
class ExportClient extends Model implements Authenticatable, AuthorizableInterface
{
    use Authorizable;
    use CamelCaseAttributes;
    use HasFactory;

    protected $table = 'export_client';
    public $timestamps = false;
    protected $casts = [
        'pseudo_id_key_pair' => SealedWithKey::class . ':' . SecurityModule::SK_EXPORT_CLIENT,
        'pseudo_id_nonce' => SealedWithKey::class . ':' . SecurityModule::SK_EXPORT_CLIENT,
    ];

    public function organisations(): BelongsToMany
    {
        return
            $this
            ->belongsToMany(EloquentOrganisation::class, 'export_client_organisation', 'export_client_id', 'organisation_uuid')
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function purposes(): HasMany
    {
        return $this->hasMany(ExportClientPurpose::class);
    }

    public function getPurposeLimitation(): PurposeLimitation
    {
        $builder = PurposeLimitationBuilder::create();
        foreach ($this->purposes as $purpose) {
            assert($purpose instanceof ExportClientPurpose);
            $builder->addPurpose($purpose->purpose);
        }

        return $builder->build();
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        throw new RuntimeException('Unsupported');
    }

    public function getRememberToken(): string
    {
        throw new RuntimeException('Unsupported');
    }

    /**
     * @inheritdoc
     */
    public function setRememberToken($value): void
    {
        throw new RuntimeException('Unsupported');
    }

    public function getRememberTokenName(): string
    {
        throw new RuntimeException('Unsupported');
    }

    public static function newFactory(): ExportClientFactory
    {
        return ExportClientFactory::new();
    }
}
