<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Hospital\HospitalCommon;
use App\Rules\IsAfterOrEqualRule;
use App\Rules\IsUnder18InICURule;
use App\Rules\IsUnder4AdmittedAtHospitalRule;
use App\Schema\Conditions\Condition;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use Carbon\CarbonImmutable;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

use function app;
use function is_string;
use function sprintf;

class Hospital extends AbstractCovidCaseFragment implements HospitalCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Hospital');
        $schema->setDocumentationIdentifier('covidCase.hospital');

        $isAdmitted = $schema->add(YesNoUnknown::getVersion(1)->createField('isAdmitted'));

         $isAdmitted->getValidationRules()
             ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('index-dateOfBirth'))) {
                    return new IsUnder4AdmittedAtHospitalRule(new CarbonImmutable($context->getValue('index-dateOfBirth')));
                }
                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $hasBeenAdmitted = Condition::field($isAdmitted)->identicalTo(YesNoUnknown::yes());

        $schema->add(StringType::createField('name'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('max:500');

        $schema->add(StringType::createField('location'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('max:250');

        $schema->add(DateTimeType::createField('admittedAt', 'Y-m-d'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('required_with:releasedAt,admittedInICUAt')
            ->addWarning('before_or_equal:today', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            )
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('startOfCovidSurveillanceDate')) {
                    Assert::string($context->getValue('startOfCovidSurveillanceDate'));
                    return new IsAfterOrEqualRule(
                        CarbonImmutable::parse($context->getValue('startOfCovidSurveillanceDate')),
                        'Hospital admission should not be after start of surveillance.',
                    );
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('index-dateOfBirth')) {
                    Assert::string($context->getValue('index-dateOfBirth'));
                    return new IsAfterOrEqualRule(
                        CarbonImmutable::parse($context->getValue('index-dateOfBirth')),
                        'Hospital admission should not be before date of birth.',
                    );
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addNotice(static function (ValidationContext $context) {
                if ($context->getValue('test-dateOfTest')) {
                    return 'before_or_equal:test-dateOfTest';
                }
                return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(DateTimeType::createField('releasedAt', 'Y-m-d'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning('after_or_equal:admittedAt');

        $schema->add(HospitalReason::getVersion(1)->createField('reason'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE)
            ->getValidationRules();

            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
            // ->addWarning(
            //     sprintf('requiredIf:isAdmitted,%s', YesNoUnknown::yes()),
            //     [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            // )
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END


        $hasGivenPermission = $schema->add(YesNoUnknown::getVersion(1)->createField('hasGivenPermission'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE);

        $hasGivenPermissionIsYes = $hasBeenAdmitted->and(Condition::field($hasGivenPermission)->identicalTo(YesNoUnknown::yes()));

        $schema->add(StringType::createField('practitioner'))
            ->setEncodingCondition($hasGivenPermissionIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(StringType::createField('practitionerPhone'))
            ->setEncodingCondition($hasGivenPermissionIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('phone:INTERNATIONAL,NL')
            ->addFatal('max:25');

        $isInICU = $schema->add(YesNoUnknown::getVersion(1)->createField('isInICU'))
            ->setEncodingCondition($hasBeenAdmitted, EncodingContext::MODE_STORE);
        $isInICU->getValidationRules()
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
            // ->addWarning(
            //     sprintf('requiredIf:isAdmitted,%s', YesNoUnknown::yes()),
            //     [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            // )
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
            ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('index-dateOfBirth'))) {
                    return new IsUnder18InICURule(new CarbonImmutable($context->getValue('index-dateOfBirth')));
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);


        $schema->add(DateTimeType::createField('admittedInICUAt', 'Y-m-d'))
            ->setEncodingCondition(
                $hasBeenAdmitted->and(Condition::field($isInICU)->identicalTo(YesNoUnknown::yes())),
                EncodingContext::MODE_STORE,
            )
            ->getValidationRules()
            ->addWarning('before_or_equal:today', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning(
                static fn (ValidationContext $context): array =>
                $context->getValue('releasedAt') ? ['before_or_equal:releasedAt'] : [],
            )
            ->addWarning('after_or_equal:admittedAt', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addNotice(static function (ValidationContext $context) {
                if ($context->getValue('test-dateOfTest')) {
                    return 'before_or_equal:test-dateOfTest';
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function onSaving(): void
    {
        if ($this->practitionerPhone !== null) {
            $this->practitionerPhone = PhoneFormatter::format($this->practitionerPhone);
        }

        parent::onSaving();
    }
}
