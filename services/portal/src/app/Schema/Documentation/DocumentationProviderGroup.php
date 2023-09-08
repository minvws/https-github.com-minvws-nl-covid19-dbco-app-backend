<?php

declare(strict_types=1);

namespace App\Schema\Documentation;

use SplObjectStorage;

class DocumentationProviderGroup implements DocumentationProvider
{
    /** @var SplObjectStorage<DocumentationProvider> */
    private SplObjectStorage $providers;

    public function __construct()
    {
        $this->providers = new SplObjectStorage();
    }

    public function attach(DocumentationProvider $provider): void
    {
        $this->providers->attach($provider);
    }

    public function detach(DocumentationProvider $provider): void
    {
        $this->providers->detach($provider);
    }

    public function getDocumentation(string $identifier, string $key): ?string
    {
        foreach ($this->providers as $provider) {
            $result = $provider->getDocumentation($identifier, $key);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
