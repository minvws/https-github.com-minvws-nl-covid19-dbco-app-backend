<?php

declare(strict_types=1);

namespace App\Schema\Generator\TypeScript;

use App\Schema\Generator\Base\VersionClass;
use App\Schema\Generator\Base\VersionInterface;

use function array_map;
use function count;
use function implode;
use function strlen;

/**
 * Represents a version of a schema object.
 *
 * @extends VersionTypeCodeGenerator<VersionClass>
 */
class VersionClassCodeGenerator extends VersionTypeCodeGenerator
{
    /**
     * Returns an extends clause based for the registered interfaces.
     *
     * This includes the implements keyword and spacing. If no interfaces have been registered,
     * an empty string is returned. This means the result of this method can easily be concatenated
     * in generated code.
     */
    protected function getVersionUnions(): string
    {
        if (count($this->getVersion()->getInterfaces()) === 0) {
            return 'any';
        }

        $unions = array_map(static fn (VersionInterface $i) => $i->getShortName(), $this->getVersion()->getInterfaces());

        return implode(' & ', $unions);
    }

    public function getCode(): string
    {
        $code = "/**\n * *** WARNING ***\n * This code is auto-generated. Any changes will be reverted by generating the schema!\n */\n\n";
        $interfaceImports = $this->getInterfaceImports();

        $code .= "import { DTO } from '@dbco/schema/dto';\n";

        if (strlen($interfaceImports) > 0) {
            $code .= $interfaceImports;
            $code .= "\n\n";
        }

        $code .= "export type {$this->getVersion()->getShortName()} = {$this->getVersionUnions()};\n";
        $code .= "\n";
        $code .= "export type {$this->getVersion()->getShortName()}DTO = DTO<{$this->getVersion()->getShortName()}>;\n";

        return $code;
    }
}
