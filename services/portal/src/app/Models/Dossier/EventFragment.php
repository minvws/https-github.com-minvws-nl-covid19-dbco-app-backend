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
 * @property int $dossier_event_id
 * @property string $name
 * @property string $data
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $expires_at
 *
 * @property Event $event
 */
class EventFragment extends FragmentModel
{
    private const OWNER_TABLE_COLUMN = 'dossier_event_id';

    protected $table = 'dossier_event_fragment';

    protected static function getStorageTerm(): StorageTerm
    {
        return StorageTerm::long();
    }

    protected static function getOwnerTableColumn(): string
    {
        return self::OWNER_TABLE_COLUMN;
    }

    protected function loadSchemaVersion(): SchemaVersion
    {
        return $this->event->getFragmentSchemaVersion($this->name);
    }

    protected function getEncryptionReferenceDate(): DateTimeInterface
    {
        return $this->event->createdAt;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, self::OWNER_TABLE_COLUMN);
    }
}
