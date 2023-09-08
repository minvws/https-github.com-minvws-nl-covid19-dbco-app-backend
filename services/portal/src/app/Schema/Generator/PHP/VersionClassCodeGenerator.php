<?php

declare(strict_types=1);

namespace App\Schema\Generator\PHP;

use App\Schema\Generator\Base\VersionClass;

/**
 * Represents a version of a schema object.
 *
 * @extends VersionTypeCodeGenerator<VersionClass>
 */
class VersionClassCodeGenerator extends VersionTypeCodeGenerator
{
    public function getCode(): string
    {
        return
            "<?php\n\n" .
            "namespace {$this->getVersion()->getNamespaceName()};\n\n" .
            "/**\n" .
            " * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***\n" .
            " */\n" .
            "class {$this->getVersion()->getShortName()} extends \\{$this->getVersion()->getSchema()->getClass()}{$this->getImplementsClause()}\n" .
            "{\n" .
            "}\n\n";
    }
}
