<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Schema;

use function array_filter;

/**
 * Can be used to collect schema object subclass versions.
 *
 * @extends VersionTypeCollector<VersionClass>
 */
class VersionClassCollector extends VersionTypeCollector
{
    private array $interfaces;

    /**
     * @param array $interfaces
     */
    public function __construct(Schema $schema, array $interfaces)
    {
        parent::__construct($schema);

        $this->interfaces = $interfaces;
    }

    /**
     * Returns the interfaces available to classes to implement.
     *
     * @return array
     */
    protected function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * Collect class versions.
     *
     * @return array
     */
    protected function collect(): array
    {
        $interfaces = $this->getInterfaces();
        $classes = [];
        for ($version = $this->getSchema()->getMinVersion()->getVersion(); $version <= $this->getSchema()->getMaxVersion()->getVersion(); $version++) {
            $versionInterfaces = array_filter(
                $interfaces,
                static fn (VersionInterface $interface) =>
                    $interface->getMinVersion() <= $version &&
                    (!$interface instanceof UpToVersionInterface || $interface->getMaxVersion() >= $version)
            );

            $class = new VersionClass($this->getSchema(), $version, $versionInterfaces);
            $classes[$class->getShortName()] = $class;
        }

        return $classes;
    }
}
