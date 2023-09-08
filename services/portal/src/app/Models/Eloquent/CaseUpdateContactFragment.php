<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CaseUpdateContactFragment\CaseUpdateContactFragmentCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Fields\Field;
use App\Schema\Fragment;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\AnyType;
use App\Schema\Types\IntType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\StringType;
use App\Schema\Update\Update;
use App\Schema\Update\UpdateDiff;
use App\Schema\Update\UpdateException;
use App\Schema\Update\UpdateValidationException;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\SealedJson;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function app;
use function sprintf;

/**
 * @property string $case_update_contact_uuid
 * @property string $name
 * @property array $data
 * @property int $version
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property CaseUpdateContact $caseUpdateContact
 */
class CaseUpdateContactFragment extends EloquentBaseModel implements SchemaObject, SchemaProvider, CaseUpdateContactFragmentCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'case_update_contact_fragment';
    protected $primaryKey = ['case_update_contact_uuid', 'name'];
    protected $casts = [
        'data' => SealedJson::class . ':' . StorageTerm::SHORT . ',received_at,' . SealedJson::DECODE_OBJECTS_AS_ASSOCIATIVE_ARRAY,
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (self $model): void {
            $model->received_at = $model->caseUpdateContact->received_at;
        });
    }

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CaseUpdateContactFragment');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(StringType::createField('name'))->setAllowsNull(false);
        $schema->add(AnyType::createField('data', 'array'))->setAllowsNull(false);
        $schema->add(IntType::createField('version'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function caseUpdateContact(): BelongsTo
    {
        return $this->belongsTo(CaseUpdateContact::class, 'case_update_contact_uuid');
    }

    /**
     * @return Field<SchemaType>
     *
     * @throws UpdateException
     */
    private function getFragmentField(): Field
    {
        $schemaVersion = $this->caseUpdateContact->caseUpdate->case->getTaskSchemaVersion();

        $field = $schemaVersion->getField($this->name);
        $fieldType = $field !== null ? $field->getType() : null;
        if ($field === null || !$fieldType instanceof SchemaType) {
            throw new UpdateException(
                sprintf(
                    'Fragment "%s" does not exist for "%s" schema version %d!',
                    $this->name,
                    $schemaVersion->getClass(),
                    $schemaVersion->getVersion(),
                ),
            );
        }

        return $field;
    }

    /**
     * @param Field<SchemaType> $field
     *
     * @throws UpdateException
     */
    private function getFragment(?EloquentTask $contact, Field $field): Fragment
    {
        $schemaVersion = $field->getType()->getSchemaVersion();

        $fragment = $contact ? $field->assignedValue($contact) : $schemaVersion->newInstance();
        if ($fragment instanceof Fragment) {
            return $fragment;
        }

        throw new UpdateException(
            sprintf(
                'Fragment "%s" is not valid for "%s" schema version %d!',
                $this->name,
                $schemaVersion->getClass(),
                $schemaVersion->getVersion(),
            ),
        );
    }

    /**
     * @throws UpdateValidationException
     * @throws UpdateException
     */
    public function toUpdateDiff(): UpdateDiff
    {
        $field = $this->getFragmentField();
        $schemaVersion = $field->getType()->getSchemaVersion();
        $fragment = $this->getFragment(null, $field);
        return (new Update($schemaVersion, $this->data))->getDiff($fragment);
    }

    /**
     * @throws UpdateValidationException
     * @throws UpdateException
     */
    public function applyToContact(EloquentTask $contact, array $fields): void
    {
        $field = $this->getFragmentField();
        $schemaVersion = $field->getType()->getSchemaVersion();
        $fragment = $this->getFragment($contact, $field);
        $update = new Update($schemaVersion, $this->data);
        $update->setFields($fields);
        $update->apply($fragment);
    }
}
