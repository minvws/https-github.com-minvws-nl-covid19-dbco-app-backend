<?php

declare(strict_types=1);

namespace App\Models\Fields;

use Closure;
use MinVWS\Codable\EncodingContext;

use function assert;
use function call_user_func;
use function is_callable;
use function is_string;

class Pseudonymizer
{
    private const PSEUDO_ID_CALLBACK = self::class . '#pseudoIdCallback';

    public static function pseudonimizeForContext(
        string $id,
        EncodingContext $context,
    ): string {
        $callback = $context->getValue(self::PSEUDO_ID_CALLBACK);

        assert(is_callable($callback));
        assert(is_string($id));
        $pseudoId = call_user_func($callback, $id);
        assert(is_string($pseudoId));

        return $pseudoId;
    }

    public static function registerInContext(Closure $closure, EncodingContext $context): void
    {
        $context->setValue(self::PSEUDO_ID_CALLBACK, $closure);
    }
}
