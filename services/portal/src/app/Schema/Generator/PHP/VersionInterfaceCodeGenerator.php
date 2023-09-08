<?php

declare(strict_types=1);

namespace App\Schema\Generator\PHP;

use App\Schema\Generator\Base\VersionInterface;

/**
 * Represents an interface a schema object can implement for a certain version.
 *
 * @extends VersionTypeCodeGenerator<VersionInterface>
 */
class VersionInterfaceCodeGenerator extends VersionTypeCodeGenerator
{
    public function getCode(): string
    {
        $code = "<?php\n\n" .
            "namespace {$this->getVersion()->getNamespaceName()};\n\n" .
            "/**\n" .
            " * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***\n" .
            " *\n";

        foreach ($this->getVersion()->getFields() as $field) {
            $code .= ' * ' . $field->getAnnotation() . "\n";
        }

        $code .= " */\n" .
            "interface {$this->getVersion()->getShortName()}{$this->getImplementsClause('extends')}\n" .
            "{\n" .
            "}\n\n";

        return $code;
    }
}
