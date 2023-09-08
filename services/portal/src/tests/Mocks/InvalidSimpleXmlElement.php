<?php

declare(strict_types=1);

namespace Tests\Mocks;

use SimpleXMLElement;

class InvalidSimpleXmlElement extends SimpleXMLElement
{
    public function asXML(?string $filename = null): string|bool
    {
        return false;
    }
}
