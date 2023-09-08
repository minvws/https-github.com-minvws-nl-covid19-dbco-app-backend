<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\Validation;

use App\Schema\Validation\ValidationMessageFormatter;
use Tests\Unit\UnitTestCase;

use function sprintf;

class ValidationMessageFormatterTest extends UnitTestCase
{
    public function testAttributePlaceholderArePrefixed(): void
    {
        $prefix = $this->faker->word();
        $actual = [
            0 => 'No attribute placeholder in indexed value',
            'foo' => 'No attribute placeholder in property value',
            1 => ':Attribute placeholder in indexed value',
            'bar' => ':Attribute placeholder in property value',
            2 => 'Label with :Attribute but not at pos 0',
            3 => [
                0 => 'No attribute placeholder in nested indexed value',
                'foo' => 'No attribute placeholder in nested property value',
                1 => ':Attribute placeholder in nested indexed value',
                'bar' => ':Attribute placeholder in nested property value',
                2 => 'Nested label with :Attribute but not at pos 0',
                3 => [
                    0 => 'No attribute placeholder in further nested indexed value',
                    'foo' => 'No attribute placeholder in further nested property value',
                    1 => ':Attribute placeholder in further nested indexed value',
                    'bar' => ':Attribute placeholder in further nested property value',
                    2 => 'Further nested label with :Attribute but not at pos 0',
                ],
            ],
        ];

        $expected = [
            0 => 'No attribute placeholder in indexed value',
            'foo' => 'No attribute placeholder in property value',
            1 => sprintf('%s ":Attribute" placeholder in indexed value', $prefix),
            'bar' => sprintf('%s ":Attribute" placeholder in property value', $prefix),
            2 => 'Label with :Attribute but not at pos 0',
            3 => [
                0 => 'No attribute placeholder in nested indexed value',
                'foo' => 'No attribute placeholder in nested property value',
                1 => sprintf('%s ":Attribute" placeholder in nested indexed value', $prefix),
                'bar' => sprintf('%s ":Attribute" placeholder in nested property value', $prefix),
                2 => 'Nested label with :Attribute but not at pos 0',
                3 => [
                    0 => 'No attribute placeholder in further nested indexed value',
                    'foo' => 'No attribute placeholder in further nested property value',
                    1 => sprintf('%s ":Attribute" placeholder in further nested indexed value', $prefix),
                    'bar' => sprintf('%s ":Attribute" placeholder in further nested property value', $prefix),
                    2 => 'Further nested label with :Attribute but not at pos 0',
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            ValidationMessageFormatter::prefixLabel($prefix, $actual),
        );
    }
}
