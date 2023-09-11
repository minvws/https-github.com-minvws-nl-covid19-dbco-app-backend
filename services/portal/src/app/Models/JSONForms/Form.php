<?php

declare(strict_types=1);

namespace App\Models\JSONForms;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class Form implements Encodable
{
    public function __construct(public readonly object $dataSchema, public readonly object $uiSchema, public readonly object $translations)
    {
    }

    public function encode(EncodingContainer $container): void
    {
        $container->dataSchema = $this->dataSchema;
        $container->uiSchema = $this->uiSchema;
        $container->translations = $this->translations;
    }
}
