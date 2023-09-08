<?php

declare(strict_types=1);

namespace App\Schema\Documentation\Traits;

use App\Schema\Documentation\Documentation;
use ReflectionClass;

use function lcfirst;

trait HasDocumentation
{
    private ?string $documentationIdentifier = null;
    private ?Documentation $documentation = null;

    final public function getDocumentation(): Documentation
    {
        if ($this->documentation === null) {
            $this->documentation = new Documentation($this->getDocumentationIdentifiers());
        }

        return $this->documentation;
    }

    final public function getDocumentationIdentifier(): string
    {
        return $this->documentationIdentifier ?? $this->getDefaultDocumentationIdentifier();
    }

    final public function setDocumentationIdentifier(string $identifier): void
    {
        $this->documentationIdentifier = $identifier;
    }

    protected function getDefaultDocumentationIdentifier(): string
    {
        $refClass = new ReflectionClass($this);
        return lcfirst($refClass->getShortName());
    }

    protected function getDocumentationIdentifiers(): array
    {
        return [$this->getDocumentationIdentifier()];
    }
}
