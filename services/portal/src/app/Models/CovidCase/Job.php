<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Job\JobCommon;
use App\Rules\Job\IsCareProfessionalAgeRule;
use App\Schema\Conditions\Condition;
use App\Schema\Conditions\OrCondition;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use Carbon\CarbonImmutable;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\JobSectorGroup;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

use function app;
use function collect;

class Job extends AbstractCovidCaseFragment implements JobCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Job');
        $schema->setDocumentationIdentifier('covidCase.job');

        $wasAtJob = $schema->add(YesNoUnknown::getVersion(1)->createField('wasAtJob'));

        $wasAtJobYes = Condition::field($wasAtJob)->identicalTo(YesNoUnknown::yes());

        $sectors = $schema->add(JobSector::getVersion(1)->createArrayField('sectors'))
            ->setEncodingCondition($wasAtJobYes, EncodingContext::MODE_STORE);

        $sectors->getValidationRules()
            ->addNotice(static function (ValidationContext $context) {
                 Assert::nullOrString($context->getValue('index-dateOfBirth'));
                 return new IsCareProfessionalAgeRule(
                     $context->getValue('index-dateOfBirth') ? CarbonImmutable::parse($context->getValue('index-dateOfBirth')) : null,
                     16,
                     'isBefore',
                     'This care professional is under the age of 16 years.',
                 );
            }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])
             ->addNotice(static function (ValidationContext $context) {
                 Assert::nullOrString($context->getValue('index-dateOfBirth'));
                 return new IsCareProfessionalAgeRule(
                     $context->getValue('index-dateOfBirth') ? CarbonImmutable::parse($context->getValue('index-dateOfBirth')) : null,
                     65,
                     'isAfter',
                     'This care professional is older than 65 years.',
                 );
             }, [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL]);


        $careConditions = collect(JobSectorGroup::care()->categories)->map(
            static fn(JobSector $sector) => Condition::field($sectors)->contains($sector)
        );
        $isInCareCondition = new OrCondition(...$careConditions->all());

        $schema->add(ProfessionCare::getVersion(1)->createField('professionCare'))
            ->setEncodingCondition($wasAtJobYes->and($isInCareCondition), EncodingContext::MODE_STORE);

        $hasOtherJob = $wasAtJobYes->and(Condition::field($sectors)->contains(JobSector::andereBeroep()));
        $closeContactAtJob = $schema->add(YesNoUnknown::getVersion(1)->createField('closeContactAtJob'))
            ->setEncodingCondition($hasOtherJob, EncodingContext::MODE_STORE);

        $hadCloseContactAtOtherJob = $hasOtherJob->and(Condition::field($closeContactAtJob)->identicalTo(YesNoUnknown::yes()));
        $professionOther = $schema->add(ProfessionOther::getVersion(1)->createField('professionOther'))
            ->setEncodingCondition($hadCloseContactAtOtherJob, EncodingContext::MODE_STORE);

        $schema->add(StringType::createField('otherProfession'))
            ->setEncodingCondition(
                $hadCloseContactAtOtherJob->and(Condition::field($professionOther)->identicalTo(ProfessionOther::anders())),
                EncodingContext::MODE_STORE,
            )
            ->getValidationRules()->addWarning('max:255');

        $schema->add(StringType::createField('particularities'))
            ->setEncodingCondition($wasAtJobYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
