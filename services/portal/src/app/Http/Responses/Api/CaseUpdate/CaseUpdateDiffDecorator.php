<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\CaseUpdate;

use App\Models\CaseUpdate\CaseUpdateDiff;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class CaseUpdateDiffDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof CaseUpdateDiff);

        $container->update = $value->getCaseUpdate();
        $container->fragments = $value->getFragmentDiffs();
        $container->contacts = $value->getContactDiffs();
    }
}
