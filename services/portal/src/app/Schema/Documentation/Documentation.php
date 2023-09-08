<?php

declare(strict_types=1);

namespace App\Schema\Documentation;

class Documentation
{
    private static ?DocumentationProvider $provider = null;
    private array $identifiers;

    /**
     * Returns the current documentation provider.
     */
    public static function getProvider(): ?DocumentationProvider
    {
        return self::$provider;
    }

    /**
     * Sets the current documentation provider.
     */
    public static function setProvider(?DocumentationProvider $provider): void
    {
        self::$provider = $provider;
    }

    /**
     * Returns a translation.
     */
    public static function get(string $namespace, string $key): ?string
    {
        $provider = self::getProvider();
        if ($provider === null) {
            return null;
        }

        return $provider->getDocumentation($namespace, $key);
    }

    public function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getLabel(): ?string
    {
        return $this->getByKey('label');
    }

    public function getShortDescription(): ?string
    {
        return $this->getByKey('shortDescription');
    }

    public function getDescription(): ?string
    {
        return $this->getByKey('description');
    }

    public function getByKey(string $key, ?string $fallback = null): ?string
    {
        $provider = self::getProvider();
        if ($provider === null) {
            return $fallback;
        }

        foreach ($this->getIdentifiers() as $identifier) {
            $result = $provider->getDocumentation($identifier, $key);
            if ($result !== null) {
                return $result;
            }
        }

        return $fallback;
    }
}
