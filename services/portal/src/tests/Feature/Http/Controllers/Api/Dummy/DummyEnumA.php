<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Dummy;

use MinVWS\DBCO\Enum\Models\Enum;

class DummyEnumA extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'phpClass' => 'DummyEnumA',
            'tsConst' => 'dummyEnumA',
            'description' => 'Dummy enum A',
            'default' => 'A',
            'items' =>
                [
                    0 =>
                        (object) [
                            'label' => 'A',
                            'value' => 'a',
                            'name' => 'a',
                        ],
                    1 =>
                        (object) [
                            'label' => 'B',
                            'value' => 'b',
                            'name' => 'b',
                        ],
                ],
        ];
    }
}
