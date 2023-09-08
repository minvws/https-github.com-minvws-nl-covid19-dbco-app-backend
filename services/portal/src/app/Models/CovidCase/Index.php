<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Helpers\JobSectorHelper;
use App\Helpers\SearchableHash;
use App\Models\Eloquent\CovidCaseSearch;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Index\IndexCommon;
use App\Observers\IndexObserver;
use App\Observers\IndexSearchHashObserver;
use App\Rules\IsBeforeOrEqualRule;
use App\Rules\IsBeforeRule;
use App\Rules\IsCareProfessionalAgeRule;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\NoBsnOrAddressReason;
use Webmozart\Assert\Assert;

use function app;
use function is_string;
use function sprintf;

class Index extends AbstractCovidCaseFragment implements IndexCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Index');
        $schema->setDocumentationIdentifier('covidCase.index');

        $schema->add(StringType::createField('initials'));

        $schema->add(StringType::createField('firstname'))
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('max:250');

        $schema->add(StringType::createField('lastname'))
            ->getValidationRules()
            ->addFatal('required')
            ->addWarning('max:500');

        $schema->add(StringType::createField('bsnNotes'))
            ->getValidationRules()
            ->addFatal('max:5000');

        $schema->add(DateTimeType::createField('dateOfBirth', 'Y-m-d'))
            ->getValidationRules()
            ->addFatal('date_format:Y-m-d')
            ->addWarning('required', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning('before_or_equal:today', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(
                sprintf('after_or_equal:%s', '1906-01-01'),
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addWarning(static function (ValidationContext $context) {
                if (is_string($context->getValue('caseCreationDate'))) {
                    return new IsBeforeRule(
                        CarbonImmutable::parse($context->getValue('caseCreationDate')),
                        'Date of birth may not be on case creation date or after.',
                    );
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('test-dateOfSymptomOnset')) {
                    return 'before_or_equal:test-dateOfSymptomOnset';
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hospital-admittedAt')) {
                    Assert::string($context->getValue('hospital-admittedAt'));
                    return new IsBeforeOrEqualRule(
                        CarbonImmutable::parse($context->getValue('hospital-admittedAt')),
                        'Date of birth should not be after admission at hospital.',
                    );
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addNotice(
                static function (ValidationContext $context) {
                     return new IsCareProfessionalAgeRule(
                         (bool) $context->getValue('isCareProfessional'),
                         16,
                         'isBefore',
                         'This care professional is under the age of 16 years.',
                     );
                },
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addNotice(
                static function (ValidationContext $context) {
                     return new IsCareProfessionalAgeRule(
                         (bool) $context->getValue('isCareProfessional'),
                         65,
                         'isAfter',
                         'This care professional is older than 65 years.',
                     );
                },
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addNotice(
                new IsBeforeRule(CarbonImmutable::parse('-6 months'), 'This person is under the age of 6 months.'),
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            );

        $schema->add(Gender::getVersion(1)->createField('gender'));

        $schema->add(IndexAddress::getSchema()->getVersion(1)->createField('address'));

        $schema->add(StringType::createField('bsnCensored'))
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context): array =>
                    $context->getValue('bsnCensored') ? ['min:8', 'max:9'] : [],
            );

        $schema->add(StringType::createField('bsnLetters'))
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context): array =>
                    $context->getValue('bsnLetters') ? ['max:25'] : [],
            );

        $schema->add(BoolType::createField('hasNoBsnOrAddress'))->setMaxVersion(1);
        $schema->add(NoBsnOrAddressReason::getVersion(1)->createArrayField('hasNoBsnOrAddress'))->setMinVersion(2);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::saving(static fn (self $index) => $index->updateSearchHash());
        self::observe(IndexObserver::class);
        self::observe(IndexSearchHashObserver::class);
    }

    public function search(): HasMany
    {
        return $this->hasMany(CovidCaseSearch::class, foreignKey: 'covidcase_uuid', localKey: 'case_uuid');
    }

    public function updateSearchHash(): void
    {
        $hash = empty($this->lastname) || empty($this->dateOfBirth)
            ? null
            : app()
                ->get(SearchableHash::class)
                ->hashForLastNameAndDateOfBirth($this->lastname, $this->dateOfBirth);

        if ($this->covidCase->search_date_of_birth === $hash) {
            return;
        }

        $this->covidCase->search_date_of_birth = $hash;
        $this->covidCase->save();
    }

    public function isCareProfessional(): bool
    {
        return $this->covidCase->job->sectors !== null && JobSectorHelper::containsCareGroup($this->covidCase->job->sectors);
    }
}
