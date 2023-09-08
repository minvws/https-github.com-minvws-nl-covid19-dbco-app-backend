<?php

namespace DBCO\Shared\Tests\Helpers;

use DBCO\Shared\Application\Helpers\PhoneFormatter;

/**
 * Class PhoneFormatterTest
 * @package DBCO\Shared\Tests\Helpers
 *
 * @group phone
 */
class PhoneFormatterTest extends \Tests\TestCase
{
    /**
     * @dataProvider phoneProvider
     */
    public function testPhoneNumberFormatterShouldReturnFormattedNumber(
        string $phoneNumberOrigin,
        string $phoneNumberExpected
    ) {
        $this->assertEquals($phoneNumberExpected, PhoneFormatter::format($phoneNumberOrigin));
    }

    /**
     * @return array<int, array<int, string|false>>
     */
    public static function phoneProvider(): array
    {
        return [
            ['020 234 56 78', '020 234 5678'],
            ['0202345678', '020 234 5678'],
            ['020-234-56-78', '020 234 5678'],
            ['+31 46 4431234', '046 443 1234'],
            ['0031 46 4431234', '046 443 1234'],
            ['++31 46 4431234', '046 443 1234'],
            ['+31 20 234 56 78', '020 234 5678'],
            ['+31 20 234 56 78', '020 234 5678'],
            ['+31 20 234-56-78', '020 234 5678'],
            ['+31 020 234-56-78', '020 234 5678'],
            ['06 23 41 56 78', '06 23415678'],
            ['06/23 41 56 78', '06 23415678'],
            ['06-23-41-56-78', '06 23415678'],
            ['06-23-41a56-78', '06 23415678'],
            ['+49 (06442) 3933023', '+49 6442 3933023'],
            ['+49 (02852) 5996-0', '+49 2852 59960'],
            ['+49 (042) 1818 87 9919', '+49 421 818879919'],
            ['+49 0644123', '+49 6441 23'],
            ['+49 06442abc', '+49 6442 222'],
        ];
    }

    /**
     * @dataProvider invalidPhoneProvider
     */
    public function testPhoneNumberFormatterReturnsUnformattedNumberForInvalidNumber(
        string $originalPhonenumber,
        string $expectedPhonenumber,
    ) {
        $this->assertEquals($expectedPhonenumber, PhoneFormatter::format($originalPhonenumber));
    }

    /**
     * @return array<int, array<int, string|false>>
     */
    public static function invalidPhoneProvider(): array
    {
        return [
            [
                'originalPhonenumber' => '06 23 41 56 7',
                'expectedPhonenumber' => '06 23 41 56 7',
            ],
            [
                'originalPhonenumber' => '+31 6 23 41 56 7',
                'expectedPhonenumber' => '62341567',
            ],
            [
                'originalPhonenumber' => '+49 1739 34 12 8',
                'expectedPhonenumber' => '+49 173934128',
            ],
        ];
    }
}
