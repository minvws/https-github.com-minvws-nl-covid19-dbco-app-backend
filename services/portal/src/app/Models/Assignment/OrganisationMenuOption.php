<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\EncodingContainer;

class OrganisationMenuOption extends MenuOption
{
    public function encode(EncodingContainer $container): void
    {
        $container->type = 'menu';
        $container->label = "Uitbesteden";
        $container->options = $this->options;
    }
}
