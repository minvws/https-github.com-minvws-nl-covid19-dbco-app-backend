<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\EncodingContainer;

abstract class LeafOption extends SelectableOption
{
    abstract public function getLabel(): string;

    public function encode(EncodingContainer $container): void
    {
        $container->type = 'option';
        $container->label = $this->getLabel();
        $container->isSelected = $this->isSelected();
        $container->isEnabled = $this->isEnabled();
    }
}
