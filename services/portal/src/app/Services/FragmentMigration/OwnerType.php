<?php

declare(strict_types=1);

namespace App\Services\FragmentMigration;

use Closure;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

final class OwnerType
{
    private string $table;
    private StorageTerm $storageTerm;

    /** @var array<string, self> */
    private static array $instances = [];

    private function __construct(string $table, StorageTerm $storageTerm)
    {
        $this->table = $table;
        $this->storageTerm = $storageTerm;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getStorageTerm(): StorageTerm
    {
        return $this->storageTerm;
    }

    private static function getOrCreate(string $type, Closure $creator): self
    {
        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = $creator();
        }

        return self::$instances[$type];
    }

    public static function covidCase(): self
    {
        return self::getOrCreate('covidcase', static fn () => new self('covidcase', StorageTerm::long()));
    }

    public static function task(): self
    {
        return self::getOrCreate('task', static fn () => new self('task', StorageTerm::short()));
    }

    public static function context(): self
    {
        return self::getOrCreate('context', static fn () => new self('context', StorageTerm::long()));
    }
}
