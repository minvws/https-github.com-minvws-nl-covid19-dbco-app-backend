<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\CaseUpdate;

use App\Models\CaseUpdate\CaseUpdateContactDiff;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class CaseUpdateContactDiffDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof CaseUpdateContactDiff);

        $container->label = $value->getCaseUpdateContact()->label;
        $container->fragments = $value->getFragmentDiffs();
    }
}
