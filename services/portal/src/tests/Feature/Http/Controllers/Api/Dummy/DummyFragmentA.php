<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Dummy;

use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class DummyFragmentA extends FragmentCompat implements SchemaProvider
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace(self::class);
        $schema->setDocumentationIdentifier('dummy.fragment.a');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isFragmentDummy'));

        return $schema;
    }
}
