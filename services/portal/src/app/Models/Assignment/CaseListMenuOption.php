<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\EncodingContainer;

class CaseListMenuOption extends MenuOption
{
    public function encode(EncodingContainer $container): void
    {
        $container->type = 'menu';
        $container->label = "Lijsten";
        $container->options = $this->options;
        $container->isEnabled = true;
    }
}
