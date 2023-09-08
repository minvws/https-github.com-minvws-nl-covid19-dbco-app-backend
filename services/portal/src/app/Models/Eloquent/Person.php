<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Casts\EncryptedDate;
use App\Helpers\SearchableHash;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Person\ContactDetails;
use App\Models\Person\NameAndAddress;
use App\Models\Versions\Person\PersonCommon;
use App\Schema\Eloquent\Traits\HasFragment;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use function app;

/**
 * @property int $id
 * @property string $uuid
 * @property int $schema_version
 * @property CarbonImmutable $date_of_birth
 * @property ?CarbonImmutable $date_of_birth_encrypted
 * @property ?string $pseudo_bsn_guid
 * @property ?string $search_non_bsn
 * @property ?string $search_date_of_birth
 * @property ?string $search_email
 * @property ?string $search_phone
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $deleted_at
 *
 * @property ContactDetails $contactDetails
 * @property NameAndAddress $nameAndAddress
 */
class Person extends Model implements SchemaObject, SchemaProvider, PersonCommon
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;
    use SoftDeletes;
    use GeneratesUuid;
    use HasFragment;

    protected $table = 'person';

    public $timestamps = true;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'date_of_birth' => 'datetime',
        'date_of_birth_encrypted' => EncryptedDate::class,
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Person');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('dateOfBirth', 'Y-m-d'))->setAllowsNull(false);
        $schema->add(NameAndAddress::getSchema()->getVersion(1)->createField('nameAndAddress'))->setAllowsNull(false);
        $schema->add(ContactDetails::getSchema()->getVersion(1)->createField('contactDetails'))->setAllowsNull(false);
        $schema->add(StringType::createField('pseudoBsnGuid'))->setAllowsNull(true);
        $schema->add(StringType::createField('searchNonBsn'))->setAllowsNull(true);
        $schema->add(StringType::createField('searchDateOfBirth'))->setAllowsNull(true);
        $schema->add(StringType::createField('searchPhone'))->setAllowsNull(true);
        $schema->add(StringType::createField('searchEmail'))->setAllowsNull(true);
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('updatedAt'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(static fn (self $person) => $person->updateSearchHashes());
        static::saving(static fn (self $person) => $person->updateDateOfBirthEncrypted());
    }

    private function updateDateOfBirthEncrypted(): void
    {
        if ($this->isDirty('date_of_birth')) {
            $this->date_of_birth_encrypted = CarbonImmutable::instance($this->date_of_birth);
        }
    }

    private function updateSearchHashes(): void
    {
        $lastname = $this->nameAndAddress->lastname;
        if ($lastname === null) {
            $this->searchDateOfBirth = null;
            $this->searchPhone = null;
            $this->searchEmail = null;

            return;
        }

        /** @var SearchableHash $searchableHash */
        $searchableHash = app(SearchableHash::class);

        $this->searchDateOfBirth = $this->calculateSearchDateOfBirth($searchableHash, $lastname);
        $this->searchPhone = $this->calculateSearchPhone($searchableHash, $lastname);
        $this->searchEmail = $this->calculateSearchEmail($searchableHash, $lastname);
    }

    public function nameAndAddress(): HasOne
    {
        return $this->hasOneFragment(NameAndAddress::class);
    }

    public function contactDetails(): HasOne
    {
        return $this->hasOneFragment(ContactDetails::class);
    }

    private function calculateSearchDateOfBirth(SearchableHash $hash, string $lastname): string
    {
        return $hash->hashForLastNameAndDateOfBirth($lastname, $this->date_of_birth);
    }

    private function calculateSearchPhone(SearchableHash $searchableHash, string $lastname): ?string
    {
        if ($this->contactDetails->phone === null) {
            return null;
        }

        return $searchableHash->hashForLastNameAndPhone($lastname, $this->contactDetails->phone);
    }

    private function calculateSearchEmail(SearchableHash $searchableHash, string $lastname): ?string
    {
        if ($this->contactDetails->email === null) {
            return null;
        }

        return $searchableHash->hashForLastNameAndPhone($lastname, $this->contactDetails->email);
    }
}
