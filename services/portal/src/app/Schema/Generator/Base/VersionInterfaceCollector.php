<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

/**
 * Can be used to collect schema object interface versions.
 *
 * @extends VersionTypeCollector<VersionInterface>
 */
class VersionInterfaceCollector extends VersionTypeCollector
{
    /**
     * Collect interface versions.
     *
     * @return array
     */
    protected function collect(): array
    {
        $previousUpInterface = null;
        $interfaces = [];
        for ($minVersion = $this->getSchema()->getMinVersion()->getVersion(); $minVersion <= $this->getSchema()->getMaxVersion()->getVersion(); $minVersion++) {
            $interface = new UpVersionInterface($this->getSchema(), $minVersion, $previousUpInterface ? [$previousUpInterface] : []);
            $interfaces[$interface->getShortName()] = $interface;
            $previousUpInterface = $interface;

            for ($maxVersion = $this->getSchema()->getMinVersion()->getVersion(); $maxVersion < $this->getSchema()->getMaxVersion()->getVersion(); $maxVersion++) {
                $interface = new UpToVersionInterface($this->getSchema(), $minVersion, $maxVersion);
                if (!$interface->isEmpty()) {
                    $interfaces[$interface->getShortName()] = $interface;
                }
            }
        }

        return $interfaces;
    }
}
