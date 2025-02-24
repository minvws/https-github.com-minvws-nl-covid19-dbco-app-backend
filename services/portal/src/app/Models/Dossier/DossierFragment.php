<?php

declare(strict_types=1);

namespace App\Models\Dossier;

use App\Schema\SchemaVersion;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

/**
 * @property int $id
 * @property int $dossier_id
 * @property string $name
 * @property string $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $expires_at
 *
 * @property Dossier $dossier
 */
class DossierFragment extends FragmentModel
{
    private const OWNER_TABLE_COLUMN = 'dossier_id';

    protected $table = 'dossier_fragment';

    protected static function getOwnerTableColumn(): string
    {
        return self::OWNER_TABLE_COLUMN;
    }

    protected static function getStorageTerm(): StorageTerm
    {
        return StorageTerm::long();
    }

    protected function loadSchemaVersion(): SchemaVersion
    {
        return $this->dossier->getFragmentSchemaVersion($this->name);
    }

    protected function getEncryptionReferenceDate(): DateTimeInterface
    {
        return $this->dossier->createdAt;
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }
}
