<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Deceased\DeceasedCommon;
use App\Rules\IsAfterRule;
use App\Rules\IsBeforeOrEqualRule;
use App\Rules\IsDeceasedCareProfessionalRule;
use App\Schema\Conditions\Condition;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use Carbon\CarbonImmutable;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;
use function is_string;
use function sprintf;

class Deceased extends AbstractCovidCaseFragment implements DeceasedCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Deceased');
        $schema->setDocumentationIdentifier('covidCase.deceased');

        $isDeceased = $schema->add(YesNoUnknown::getVersion(1)->createField('isDeceased'))
            ->setDefaultValue(null);

         $isDeceased->getValidationRules()
             ->addNotice(static function (ValidationContext $context) {
                if ($context->getValue('isCareProfessional')) {
                    return new IsDeceasedCareProfessionalRule();
                }

                 return '';
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);
// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
//             ->addWarning(self::getDeceasedAndUnderlyingSufferingRule(), [
//                 ValidationRule::TAG_OSIRIS_FINAL,
//             ]);
// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

        $isDeceasedIsYes = Condition::field($isDeceased)->identicalTo(YesNoUnknown::yes());

        $schema->add(DateTimeType::createField('deceasedAt', 'Y-m-d'))
            ->setEncodingCondition($isDeceasedIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxBeforeCaseCreationDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxBeforeCaseCreationDate'))
                    : '',
            )
// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
//            ->addWarning(
//                sprintf('requiredIf:isDeceased,%s', YesNoUnknown::yes()->value),
//                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
//            )
// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
            ->addWarning(
                new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Deceased date should not be in the future.'),
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('startOfCovidSurveillanceDate'))
                    ? sprintf('after_or_equal:%s', $context->getValue('startOfCovidSurveillanceDate'))
                    : '',
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
            )
            ->addWarning('after_or_equal:test-dateOfSymptomOnset', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning('after_or_equal:test-dateOfTest', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning('after_or_equal:hospital-admittedAt', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addWarning('after_or_equal:hospital-admittedInICUAt', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
            ->addNotice(static function (ValidationContext $context) {
                if (is_string($context->getValue('index-dateOfBirth'))) {
                    $date45thBirthday = (new CarbonImmutable($context->getValue('index-dateOfBirth')))->add('45 years');
                    return new IsAfterRule($date45thBirthday, 'This person was under the age of 45.');
                }
                 return '';
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice('after_or_equal:hospital-admittedAt', [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);

        $schema->add(CauseOfDeath::getVersion(1)->createField('cause'))
            ->setEncodingCondition($isDeceasedIsYes, EncodingContext::MODE_STORE);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
//    private static function getDeceasedAndUnderlyingSufferingRule(): Closure
//    {
//        return static function (ValidationContext $context) {
//            Assert::nullorstring($context->getValue('index-dateOfBirth'));
//            Assert::string($context->getValue('caseCreationDate'));
//            Assert::nullorstring($context->getValue('underlyingSuffering-hasUnderlyingSuffering'));
//
//            return new DeceasedAndUnderlyingSufferingUnder70Rule(
//                CarbonImmutable::parse($context->getValue('caseCreationDate')),
//                CarbonImmutable::parse($context->getValue('index-dateOfBirth')),
//                $context->getValue('underlyingSuffering-hasUnderlyingSuffering') ?
//                    YesNoUnknown::from($context->getValue('underlyingSuffering-hasUnderlyingSuffering')) :
//                    null,
//            );
//        };
//    }
// ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
}
