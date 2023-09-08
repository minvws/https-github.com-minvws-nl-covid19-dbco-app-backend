<?php

declare(strict_types=1);

namespace App\Schema\Generator\PHP;

use App\Schema\Generator\Base\VersionInterface;
use App\Schema\Generator\Base\VersionType;
// required for PHPStan, even though PHPStorm thinks otherwise
use App\Schema\Generator\Base\VersionTypeCodeGenerator as VersionTypeCodeGeneratorBase;

use function array_map;
use function count;
use function explode;
use function implode;
use function str_replace;

/**
 * @template T of VersionType
 *
 * @extends VersionTypeCodeGeneratorBase<T>
 */
abstract class VersionTypeCodeGenerator extends VersionTypeCodeGeneratorBase
{
    public function getPath(): string
    {
        $version = $this->getVersion();
        $parts = explode('\\', str_replace('App\\Models\\Versions\\', '', $version->getNamespaceName()));
        $parts[] = $version->getShortName();
        return implode('/', $parts) . '.php';
    }

    /**
     * Returns an implements clause based for the registered interfaces.
     *
     * This includes the implements keyword and spacing. If no interfaces have been registered,
     * an empty string is returned. This means the result of this method can easily be concatenated
     * in generated code.
     */
    protected function getImplementsClause(string $implementsKeyword = 'implements'): string
    {
        if (count($this->getVersion()->getInterfaces()) === 0) {
            return '';
        }

        $interfaces = array_map(static fn (VersionInterface $i) => '\\' . $i->getName(), $this->getVersion()->getInterfaces());
        return ' ' . $implementsKeyword . ' ' . implode(', ', $interfaces);
    }
}
