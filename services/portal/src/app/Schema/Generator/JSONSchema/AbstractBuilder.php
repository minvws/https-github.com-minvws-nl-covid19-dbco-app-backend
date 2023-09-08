<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use function array_merge;

abstract class AbstractBuilder
{
    final public function build(Config $config): array
    {
        $context = new Context($config);
        return $this->buildRoot($context);
    }

    protected function buildRoot(Context $context): array
    {
        return array_merge(
            $this->buildHeader($context),
            $this->buildBody($context),
        );
    }

    final public function buildDef(Context $context): array
    {
        $header = [];
        if ($context->getUseCompoundSchemas() === UseCompoundSchemas::External) {
            $header = $this->buildHeader($context);
        }

        return array_merge(
            $header,
            $this->buildBody($context),
        );
    }

    abstract protected function buildHeader(Context $context): array;

    abstract protected function buildBody(Context $context): array;
}
