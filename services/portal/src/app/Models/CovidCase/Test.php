<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Test\TestCommon;
use App\Rules\IsAfterOrEqualRule;
use App\Rules\IsAfterRule;
use App\Rules\IsBeforeOrEqualRule;
use App\Rules\IsBeforeRule;
use App\Schema\Conditions\Condition;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use App\Services\CaseNumberService;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use MinVWS\DBCO\Enum\Models\TestReason;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

use function app;
use function is_string;
use function sprintf;

class Test extends FragmentCompat implements TestCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(4);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Test');
        $schema->setDocumentationIdentifier('covidCase.test');
        $schema->setOwnerFieldName('covidCase');

        $condition = Condition::field('covidCase.symptoms.hasSymptoms')->identicalTo(YesNoUnknown::yes());

        $schema->add(DateTimeType::createField('dateOfSymptomOnset', 'Y-m-d'))
            ->setEncodingCondition($condition, EncodingContext::MODE_STORE)
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('before_or_equal:today', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            )
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('firstAllowableDateOfSymptomOnset'))
                    ? sprintf('after_or_equal:%s', $context->getValue('firstAllowableDateOfSymptomOnset'))
                    : '',
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addWarning(static function (ValidationContext $context) {
                if (is_string($context->getValue('index-dateOfBirth'))) {
                    return new IsAfterOrEqualRule(
                        CarbonImmutable::parse($context->getValue('index-dateOfBirth')),
                        'Date of symptom onset should be after date of birth.',
                    );
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addWarning(static function (ValidationContext $context) {
                if (is_string($context->getValue('deceased-deceasedAt'))) {
                    return new IsBeforeRule(
                        CarbonImmutable::parse($context->getValue('deceased-deceasedAt')),
                        'Date of symptom onset should not be after deceased date.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('hospital-admittedAt'))) {
                    return new IsBeforeOrEqualRule(
                        CarbonImmutable::parse($context->getValue('hospital-admittedAt')),
                        'Date symptom onset should not be after hospital admission.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-dateOfResult'))) {
                    return new IsAfterRule(
                        CarbonImmutable::parse($context->getValue('test-dateOfResult'))->subDays(21),
                        'Date symptom onset is more than 21 days before lab result.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addWarning(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-previousInfectionDateOfSymptom'))) {
                    return new IsAfterRule(
                        CarbonImmutable::parse($context->getValue('test-previousInfectionDateOfSymptom')),
                        'Symptom onset should be after previous infection.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-previousInfectionDateOfSymptom'))) {
                    return new IsAfterRule(
                        CarbonImmutable::parse($context->getValue('test-previousInfectionDateOfSymptom'))->add('8 weeks'),
                        'Date of symptom onset is within 8 weeks of previous infection.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                 Assert::nullOrString($context->getValue('caseCreationDate'));
                 return new IsBeforeOrEqualRule(
                     CarbonImmutable::parse($context->getValue('caseCreationDate')),
                     'Date of symptom onset is after case creation date.',
                 );
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-previousInfectionDateOfSymptom'))) {
                    return new IsAfterRule(
                        CarbonImmutable::parse($context->getValue('test-previousInfectionDateOfSymptom'))->add('8 weeks'),
                        'Date of previous infection within 8 weeks of date of symptom onset.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('hospital-admittedInICUAt'))) {
                    return new IsBeforeOrEqualRule(
                        CarbonImmutable::parse($context->getValue('hospital-admittedInICUAt')),
                        'Date symptom onset should not be after admission at ICU.',
                    );
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(BoolType::createField('isSymptomOnsetEstimated'))
            ->setDefaultValue(false)
            ->setEncodingCondition($condition, EncodingContext::MODE_STORE);

        $schema->add(DateTimeType::createField('dateOfTest', 'Y-m-d'))
            ->setProxyForOwnerField()
            ->getValidationRules()
            ->addWarning('before_or_equal:today', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            );
        $schema->add(DateTimeType::createField('dateOfResult', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            )
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('startOfCovidSurveillanceDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('startOfCovidSurveillanceDate'))
                    : '',
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-dateOfSymptomOnset'))) {
                    return new IsBeforeRule(
                        CarbonImmutable::parse($context->getValue('test-dateOfSymptomOnset'))->addDays(21),
                        'Date symptom onset is more than 21 days before lab result.',
                    );
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(DateTimeType::createField('dateOfInfectiousnessStart', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            );

        $schema->add(StringType::createField('otherReason'))
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(InfectionIndicator::getVersion(1)->createField('infectionIndicator'));
        $schema->add(SelfTestIndicator::getVersion(1)->createField('selfTestIndicator'));
        $schema->add(LabTestIndicator::getVersion(1)->createField('labTestIndicator'));
        $schema->add(StringType::createField('otherLabTestIndicator'))
            ->getValidationRules()
            ->addWarning('max:500');
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
            // ->addWarning(
            //     sprintf('requiredIf:labTestIndicator,%s', LabTestIndicator::other()->value),
            //     [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            // )
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

        $schema->add(StringType::createField('monsterNumber'))
            ->setProxyForOwnerField('test_monster_number')
            ->getValidationRules()
            ->addWarning('max:16');
        $schema->add(DateTimeType::createField('selfTestLabTestDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:' . CarbonImmutable::now()->modify('+7 days')->format('Y-m-d'))
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
            // ->addWarning(
            //     sprintf('required_if:selfTestIndicator,%s,%s', SelfTestIndicator::molecular(), SelfTestIndicator::antigen()),
            //     [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            // )
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
            ->addWarning(
                static function (ValidationContext $context) {
                    if (is_string($context->getValue('test-dateOfTest')) && !empty($context->getValue('test-dateOfTest'))) {
                        return sprintf('after_or_equal:%s', $context->getValue('test-dateOfTest'));
                    }

                    return is_string($context->getValue('maxBeforeCaseCreationDate'))
                        ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                        : '';
                },
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-dateOfSymptomOnset'))) {
                    return new IsAfterRule(
                        CarbonImmutable::parse($context->getValue('test-dateOfSymptomOnset')),
                        'Confirmation of selfTest in lab is before date of symptom onset.',
                    );
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(TestResult::getVersion(1)->createField('selfTestLabTestResult'));

        $schema->add(YesNoUnknown::getVersion(1)->createField('isReinfection'));
        $schema->add(DateTimeType::createField('previousInfectionDateOfSymptom', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('caseCreationDate'))
                    ? sprintf('before_or_equal:%s', $context->getValue('caseCreationDate'))
                    : '',
            )
            ->addWarning(
                'after_or_equal:maxPreviousInfectionDateOfSymptom',
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addWarning(static function (ValidationContext $context) {
                if (is_string($context->getValue('test-dateOfSymptomOnset'))) {
                    return new IsBeforeRule(
                        CarbonImmutable::parse($context->getValue('test-dateOfSymptomOnset')),
                        'Previous infection date should be before symptom onset.',
                    );
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(BoolType::createField('previousInfectionSymptomFree'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('previousInfectionProven'));
        $schema->add(BoolType::createField('contactOfConfirmedInfection'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('previousInfectionReported'));
        $schema->add(StringType::createField('previousInfectionHpzoneNumber'))
            ->setMaxVersion(3)
            ->getValidationRules()
            ->addWarning('digits_between:6,8');

        $schema->add(StringType::createField('source')->setReadOnly());
        $schema->add(StringType::createField('testLocation')->setReadOnly());
        $schema->add(StringType::createField('testLocationCategory')->setReadOnly());


        // Fields up to version 1
        $schema->add(TestReason::getVersion(1)->createArrayField('reasons'))->setMaxVersion(1);

        // Fields from version 2
        $schema->add(TestReason::getVersion(2)->createArrayField('reasons'))->setMinVersion(2)->setMaxVersion(2);

        // Fields from version 3
        $schema->add(TestReason::getVersion(3)->createArrayField('reasons'))->setMinVersion(3);

        // Fields from version 4
        $schema->add(StringType::createField('previousInfectionCaseNumber'))
            ->setMinVersion(4)
            ->getValidationRules()
            ->addWarning('regex:' . CaseNumberService::CASE_NUMBER_REGEX);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPreviousInfectionCaseNumberFieldValue(?string $value, Closure $setter): void
    {
        $setter(CaseNumberService::sanitizeCaseNumber($value));
    }

    protected function getIsSymptomOnsetEstimatedFieldValue(Closure $getter): bool
    {
        if ($getter() !== null) {
            return $getter();
        }

        return (bool) $this->getOwnerProxy()->isSymptomOnsetEstimated;
    }

    protected function getDateOfTestFieldValue(Closure $getter): ?DateTimeInterface
    {
        if ($getter() !== null) {
            return $getter();
        }

        if ($this->getOwnerProxy()->indexSubmittedDateOfTest !== null) {
            $tz = new DateTimeZone('UTC');
            return new DateTimeImmutable($this->getOwnerProxy()->indexSubmittedDateOfTest, $tz);
        }

        return null;
    }

    protected function getDateOfSymptomOnsetFieldValue(Closure $getter): ?DateTimeInterface
    {
        if ($getter() !== null) {
            return $getter();
        }

        if ($this->getOwnerProxy()->indexSubmittedDateOfSymptomOnset !== null) {
            $tz = new DateTimeZone('UTC');
            return new DateTimeImmutable($this->getOwnerProxy()->indexSubmittedDateOfSymptomOnset, $tz);
        }

        return null;
    }
}
