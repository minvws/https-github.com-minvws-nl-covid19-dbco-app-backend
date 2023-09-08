<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Trip\TripCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Validation\ValidationContext;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\TransportationType;

use function app;
use function is_string;
use function sprintf;

/**
 * Used by the Abroad model.
 */
class Trip extends Entity implements SchemaProvider, TripCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Trip');
        $schema->setDocumentationIdentifier('covidCase.trip');

        $schema->add(DateTimeType::createField('departureDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxAbroadDepartureBeforeCaseCreation'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxAbroadDepartureBeforeCaseCreation'))
                    : '',
            );
        $schema->add(DateTimeType::createField('returnDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('after_or_equal:{PREFIX_PATH}departureDate');
        $schema->add(Country::getVersion(1)->createArrayField('countries'));
        $schema->add(TransportationType::getVersion(1)->createArrayField('transportation'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
