<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Schema\Types\EnumVersionType;
use MinVWS\DBCO\Enum\Models\Enum;

use function assert;
use function basename;
use function class_exists;
use function file_get_contents;
use function is_a;
use function is_array;
use function is_string;
use function json_decode;

class EnumTypeRepository
{
    public function __construct(
        private string $indexPath = __DIR__ . '/../../../../../shared/packages/dbco-enum/enums/index.json',
        private string $namespace = 'MinVWS\\DBCO\\Enum\\Models\\',
    ) {
    }

    /**
     * @return array<EnumVersionType>
     */
    public function getEnumTypes(): array
    {
        $content = file_get_contents($this->indexPath);
        assert(is_string($content));
        $enumPaths = json_decode($content, true);
        assert(is_array($enumPaths));
        $classes = [];

        foreach ($enumPaths as $path) {
            $class = $this->namespace . basename($path, '.json');
            if (class_exists($class) && is_a($class, Enum::class, true)) {
                $classes[$class] = new EnumVersionType($class::getCurrentVersion());
            }
        }

        return $classes;
    }
}
