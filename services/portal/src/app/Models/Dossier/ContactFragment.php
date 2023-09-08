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
 * @property int $dossier_contact_id
 * @property string $name
 * @property string $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $expires_at
 *
 * @property Contact $contact
 */
class ContactFragment extends FragmentModel
{
    private const OWNER_TABLE_COLUMN = 'dossier_contact_id';

    protected $table = 'dossier_contact_fragment';

    protected static function getStorageTerm(): StorageTerm
    {
        return StorageTerm::short();
    }

    protected static function getOwnerTableColumn(): string
    {
        return self::OWNER_TABLE_COLUMN;
    }

    protected function loadSchemaVersion(): SchemaVersion
    {
        return $this->contact->getFragmentSchemaVersion($this->name);
    }

    protected function getEncryptionReferenceDate(): DateTimeInterface
    {
        return $this->contact->createdAt;
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, self::OWNER_TABLE_COLUMN);
    }
}
