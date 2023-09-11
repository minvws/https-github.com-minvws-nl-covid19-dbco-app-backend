<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Model;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function strtolower;

enum DiffType implements Encodable
{
    case Added;
    case Removed;
    case Modified;

    public function encode(EncodingContainer $container): void
    {
        $container->encode(strtolower($this->name));
    }
}
