<?php

declare(strict_types=1);

namespace App\Models\Person;

use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\Person;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Person\NameAndAddress\NameAndAddressCommon;
use App\Schema\FragmentModel;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Enum\Models\Gender;

use function app;

class NameAndAddress extends FragmentModel implements NameAndAddressCommon
{
    protected $table = 'person_fragment';
    protected static string $encryptionReferenceDateAttribute = 'person.createdAt';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Person\\NameAndAddress');
        $schema->setDocumentationIdentifier('person.nameAndAddress');

        $schema->add(StringType::createField('initials'));

        $schema->add(StringType::createField('firstname'))
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('max:250');

        $schema->add(StringType::createField('lastname'))
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('max:500');

        $schema->add(DateTimeType::createField('dateOfBirth', 'Y-m-d'))
            ->getValidationRules()
            ->addFatal('required')
            ->addFatal('date_format:Y-m-d')
            ->addWarning('before_or_equal:today')
            ->addWarning('after_or_equal:1900-01-01');

        $schema->add(Gender::getVersion(1)->createField('gender'))
            ->getValidationRules()
            ->addFatal('required');

        $schema->add(IndexAddress::getSchema()->getVersion(1)->createField('address'))
            ->setDefaultValue(static fn () => IndexAddress::getSchema()->getVersion(1)->newInstance());

        $schema->add(BoolType::createField('hasNoBsnOrAddress'));

        $schema->add(StringType::createField('bsnCensored'))
            ->getValidationRules()
            ->addWarning('min:8')
            ->addWarning('max:9');

        $schema->add(StringType::createField('bsnLetters'))
            ->getValidationRules()
            ->addWarning('max:25');

        $schema->add(StringType::createField('bsnNotes'))
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
