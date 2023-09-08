<?php

declare(strict_types=1);

namespace App\Schema\Generator;

use App\Schema\Generator\Base\VersionClassCollector;
use App\Schema\Generator\Base\VersionInterfaceCollector;
use App\Schema\Generator\PHP\VersionClassCodeGenerator;
use App\Schema\Generator\PHP\VersionInterfaceCodeGenerator;
use App\Schema\Schema;

class PHPVersionCodeGenerator
{
    private function generateInterfaces(VersionInterfaceCollector $collector, string $basePath): void
    {
        foreach ($collector->getAll() as $interface) {
            $generator = new VersionInterfaceCodeGenerator($interface);
            $generator->write($basePath);
        }
    }

    private function generateClasses(VersionClassCollector $collector, string $basePath): void
    {
        foreach ($collector->getAll() as $class) {
            $generator = new VersionClassCodeGenerator($class);
            $generator->write($basePath);
        }
    }

    public function generate(Schema $schema, string $basePath): void
    {
        $interfaceCollector = new VersionInterfaceCollector($schema);
        $this->generateInterfaces($interfaceCollector, $basePath);

        $classCollector = new VersionClassCollector($schema, $interfaceCollector->getAll());
        $this->generateClasses($classCollector, $basePath);
    }
}
