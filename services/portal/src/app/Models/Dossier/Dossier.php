<?php

declare(strict_types=1);

namespace App\Models\Dossier;

use App\Models\Disease\DiseaseModel;
use App\Models\Eloquent\EloquentOrganisation;
use App\Schema\SchemaVersion;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Services\Disease\DiseaseSchemaService;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Dossier\DossierFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function app;
use function assert;

/**
 * @property int $id
 * @property int $disease_model_id
 * @property string $identifier
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Collection<int, Contact> $contacts
 * @property DiseaseModel $diseaseModel
 * @property Collection<int, Event> $events
 * @property Collection<int, DossierFragment> $fragments
 * @property EloquentOrganisation $organisation
 */
class Dossier extends FragmentOwnerModel
{
    use HasFactory;

    private const TABLE_COLUMNS = ['id', 'disease_model_id', 'organisation_uuid', 'identifier', 'created_at', 'updated_at'];

    protected $table = 'dossier';

    protected static function newFactory(): DossierFactory
    {
        return new DossierFactory();
    }

    protected function getTableColumns(): array
    {
        return self::TABLE_COLUMNS;
    }

    protected function loadSchemaVersion(): SchemaVersion
    {
        return app(DiseaseSchemaService::class)->getDossierSchema($this->diseaseModel)->getCurrentVersion();
    }

    protected function associateFragmentWithOwner(FragmentModel $fragment): void
    {
        assert($fragment instanceof DossierFragment);
        $fragment->dossier()->associate($this);
    }

    public function diseaseModel(): BelongsTo
    {
        return $this->belongsTo(DiseaseModel::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid');
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(DossierFragment::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function makeContact(): Contact
    {
        $contact = $this->getSchemaVersion()->getExpectedField('contacts')->getExpectedType(ArrayType::class)->getExpectedElementType(
            SchemaType::class,
        )->getSchemaVersion()->newInstance();
        assert($contact instanceof Contact);
        $contact->dossier()->associate($this);
        return $contact;
    }

    public function makeEvent(): Event
    {
        $event = $this->getSchemaVersion()->getExpectedField('events')->getExpectedType(ArrayType::class)->getExpectedElementType(
            SchemaType::class,
        )->getSchemaVersion()->newInstance();
        assert($event instanceof Event);
        $event->dossier()->associate($this);
        return $event;
    }
}
