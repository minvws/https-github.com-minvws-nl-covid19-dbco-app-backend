<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\GroupTransport\GroupTransportCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class GroupTransport extends FragmentCompat implements GroupTransportCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\GroupTransport');
        $schema->setDocumentationIdentifier('covidCase.groupTransport');

        $schema->add(YesNoUnknown::getVersion(1)->createField('withReservedSeats'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
