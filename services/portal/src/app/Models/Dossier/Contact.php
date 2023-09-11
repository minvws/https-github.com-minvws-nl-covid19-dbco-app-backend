<?php

declare(strict_types=1);

namespace App\Models\Dossier;

use App\Schema\SchemaVersion;
use App\Services\Disease\DiseaseSchemaService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function app;
use function assert;

/**
 * @property int $id
 * @property int $dossier_id
 * @property string $identifier
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Dossier $dossier
 * @property Collection<int, ContactFragment> $fragments
 */
class Contact extends FragmentOwnerModel
{
    private const TABLE_COLUMNS = ['id', 'dossier_id', 'created_at', 'updated_at'];

    protected $table = 'dossier_contact';

    protected function getTableColumns(): array
    {
        return self::TABLE_COLUMNS;
    }

    protected function loadSchemaVersion(): SchemaVersion
    {
        return app(DiseaseSchemaService::class)->getContactSchema($this->dossier->diseaseModel)->getCurrentVersion();
    }

    protected function associateFragmentWithOwner(FragmentModel $fragment): void
    {
        assert($fragment instanceof ContactFragment);
        $fragment->contact()->associate($this);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(ContactFragment::class, 'dossier_contact_id');
    }
}
