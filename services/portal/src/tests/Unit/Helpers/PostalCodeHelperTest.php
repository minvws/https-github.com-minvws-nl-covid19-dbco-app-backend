<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Exceptions\PostalCodeValidationException;
use App\Helpers\PostalCodeHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostalCodeHelperTest extends TestCase
{
    #[DataProvider('validPostalCodeDataProvider')]
    public function testNormalize(string $input, string $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, PostalCodeHelper::normalize($input));
    }

    #[DataProvider('validPostalCodeDataProvider')]
    public function testValidateAndNormalize(string $input, string $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, PostalCodeHelper::normalizeAndValidate($input));
    }

    public static function validPostalCodeDataProvider(): array
    {
        return [
            ['1234ab', '1234AB'],
            ['1222aB', '1222AB'],
            ['4567AB', '4567AB'],
            ['8523 XS', '8523XS'],
            ['11 22 DD', '1122DD'],
            ['9876 A B', '9876AB'],
            ['1 2 3 4 Y Z', '1234YZ'],
            ['0000AA', '0000AA'], // not a valid dutch postcode (cannot start with 0), but only formatting is validated
        ];
    }

    public function testNormalizeAndValidateFails(): void
    {
        $this->expectException(PostalCodeValidationException::class);
        $this->expectExceptionMessage('invalid postal code');

        PostalCodeHelper::normalizeAndValidate('invalid');
    }

    public function testValidateFails(): void
    {
        $this->expectException(PostalCodeValidationException::class);
        $this->expectExceptionMessage('invalid postal code');

        PostalCodeHelper::validate('invalid');
    }
}
