<?php

declare(strict_types=1);

namespace App\Models\Person;

use App\Models\Eloquent\Person;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Person\ContactDetails\ContactDetailsCommon;
use App\Schema\FragmentModel;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use Closure;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function app;

class ContactDetails extends FragmentModel implements ContactDetailsCommon
{
    protected $table = 'person_fragment';
    protected static string $encryptionReferenceDateAttribute = 'person.createdAt';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Person\\ContactDetails');
        $schema->setDocumentationIdentifier('person.contactData');

        $schema->add(StringType::createField('phone'))
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('phone:INTERNATIONAL,NL')
            ->addWarning('max:25');

        $schema->add(StringType::createField('email'))
            ->getValidationRules()
            ->addWarning('max:250');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPhoneFieldValue(?string $phone, Closure $setter): void
    {
        $setter($phone !== null ? PhoneFormatter::format($phone) : null);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
