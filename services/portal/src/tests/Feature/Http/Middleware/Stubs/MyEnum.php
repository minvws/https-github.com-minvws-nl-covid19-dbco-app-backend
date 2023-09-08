<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware\Stubs;

use MinVWS\DBCO\Enum\Models\Enum;

/**
 * @method static MyEnum foo() foo() Foo
 * @method static MyEnum bar() bar() Bar

 * @property-read string $value
*/
final class MyEnum extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'phpClass' => 'MyEnum',
            'tsConst' => 'MyEnum',
            'items' =>
            [
                (object) [
                    'label' => 'Foo',
                    'value' => 'foo',
                    'name' => 'foo',
                ],
                (object) [
                    'label' => 'Bar',
                    'value' => 'bar',
                    'name' => 'bar',
                ],
            ],
        ];
    }
}
