<?php

declare(strict_types=1);

namespace App\Schema\Documentation;

interface DocumentationProvider
{
    public function getDocumentation(string $identifier, string $key): ?string;
}
