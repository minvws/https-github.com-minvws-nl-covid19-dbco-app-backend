<?php

declare(strict_types=1);

namespace App\Schema\Generator\TypeScript;

use App\Schema\Generator\Base\VersionInterface;
use App\Schema\Generator\Base\VersionType;
// required for PHPStan, even though PHPStorm thinks otherwise
use App\Schema\Generator\Base\VersionTypeCodeGenerator as VersionTypeCodeGeneratorBase;

use function array_map;
use function count;
use function explode;
use function implode;
use function lcfirst;
use function str_replace;

/**
 * @template T of VersionType
 *
 * @extends VersionTypeCodeGeneratorBase<T>
 */
abstract class VersionTypeCodeGenerator extends VersionTypeCodeGeneratorBase
{
    /**
     * Interface that should be imported for the union.
     */
    protected function getInterfaceImports(): string
    {
        $imports = array_map(
            static fn (VersionInterface $i) => "import { " . $i->getShortName() . " } from './" . lcfirst($i->getShortName()) . "';",
            $this->getVersion()->getInterfaces(),
        );

        if (count($imports) === 0) {
            return '';
        }

        return implode("\n", $imports);
    }

    public function getPath(): string
    {
        $version = $this->getVersion();
        $parts = explode('\\', str_replace('App\\Models\\Versions\\', '', $version->getNamespaceName()));
        $parts[] = $version->getShortName();
        return implode('/', array_map('lcfirst', $parts)) . '.d.ts';
    }
}
