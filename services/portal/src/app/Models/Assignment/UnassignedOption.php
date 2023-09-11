<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\EncodingContainer;

class UnassignedOption extends LeafOption
{
    public function getLabel(): string
    {
        return 'Niet toegewezen';
    }

    public function encode(EncodingContainer $container): void
    {
        parent::encode($container);

        $container->assignment->assignedUserUuid = null;
    }

    public function isAvailable(): bool
    {
        return parent::isAvailable() || parent::isSelected();
    }
}
