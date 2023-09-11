<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Schema;
use MinVWS\Codable\DecodingContainer;

interface SchemaFactory
{
    public function createSchema(DecodingContainer $container): Schema;
}
