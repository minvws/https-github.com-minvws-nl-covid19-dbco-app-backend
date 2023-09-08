<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingCommon;
use App\Schema\Conditions\Condition;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering as UnderlyingSufferingEnum;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class UnderlyingSuffering extends FragmentCompat implements UnderlyingSufferingCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\UnderlyingSuffering');
        $schema->setCurrentVersion(2);
        $schema->setDocumentationIdentifier('covidCase.underlyingSuffering');

        $hasUnderlyingSufferingOrMedication = $schema->add(
            YesNoUnknown::getVersion(1)->createField('hasUnderlyingSufferingOrMedication'),
        );

        // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
        // $hasUnderlyingSufferingOrMedication->getValidationRules()
        //     ->addWarning(
        //         self::getDeceasedAndUnderlyingSufferingUnder70Rule(),
        //         [ValidationRule::TAG_OSIRIS_FINAL],
        //     );
        // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

        $whenUnderlyingSufferingOrMedicationIsYes = Condition::field($hasUnderlyingSufferingOrMedication)->identicalTo(YesNoUnknown::yes());

        /**
         * Use setEncodingCondition with MODE_STORE to prevent functional changes in the frontend: The condition is only applied when writing to the database, but not when sending to the frontend.
         * This way, the invalid user input is kept visible in the browser, until the user performs a page refresh.
         */
        $hasUnderlyingSuffering = $schema->add(YesNoUnknown::getVersion(1)->createField('hasUnderlyingSuffering'))
            ->setEncodingCondition($whenUnderlyingSufferingOrMedicationIsYes, EncodingContext::MODE_STORE);

        // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
        // $hasUnderlyingSuffering->getValidationRules()
        //     ->addWarning(
        //         self::getDeceasedAndUnderlyingSufferingUnder70Rule(),
        //         [ValidationRule::TAG_OSIRIS_FINAL],
        //     );
        // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

        $whenHasUnderlyingSufferingIsYes = Condition::field($hasUnderlyingSuffering)->identicalTo(YesNoUnknown::yes());

        $schema->add(StringType::createArrayField('otherItems'))
            ->setEncodingCondition(
                $whenHasUnderlyingSufferingIsYes,
                EncodingContext::MODE_STORE,
            ) // $whenUnderlyingSufferingOrMedicationIsYes->and(Condition::field('items')->contains(UnderlyingSufferingEnum::other())) // UnderlyingSufferingEnum::other does not exist yet(?), but is referenced in the spec sheet
            ->getValidationRules()
            ->addWarning('max:100')
            ->addFatal('max:5000');

        $schema->add(StringType::createField('remarks'))
            ->setEncodingCondition($whenHasUnderlyingSufferingIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning('max:5000');

        // Fields up to version 1
        $schema->add(UnderlyingSufferingEnum::getVersion(1)->createArrayField('items'))
            ->setEncodingCondition($whenHasUnderlyingSufferingIsYes, EncodingContext::MODE_STORE)
            ->setMaxVersion(1);

        // Fields starting from version 2
        $schema->add(UnderlyingSufferingEnum::getVersion(2)->createArrayField('items'))
            ->setEncodingCondition($whenHasUnderlyingSufferingIsYes, EncodingContext::MODE_STORE)
            ->setMinVersion(2);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
    // private static function getDeceasedAndUnderlyingSufferingUnder70Rule(): Closure
    // {
    //     return static function (ValidationContext $context) {
    //         Assert::nullorstring($context->getValue('index-dateOfBirth'));
    //         Assert::string($context->getValue('caseCreationDate'));
    //         Assert::nullorisInstanceOf($context->getValue('deceased-isDeceased'), YesNoUnknown::class);

    //         return new UnderlyingSufferingUnder70Rule(
    //             CarbonImmutable::parse($context->getValue('caseCreationDate')),
    //             CarbonImmutable::parse($context->getValue('index-dateOfBirth')),
    //         );
    //     };
    // }
    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
}
