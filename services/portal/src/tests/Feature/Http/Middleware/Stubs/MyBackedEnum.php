<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware\Stubs;

enum MyBackedEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
