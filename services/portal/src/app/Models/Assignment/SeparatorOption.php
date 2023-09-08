<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\EncodingContainer;

class SeparatorOption extends Option
{
    public function encode(EncodingContainer $container): void
    {
        $container->type = 'separator';
    }
}
