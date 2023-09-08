<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

use function array_map;

class PhoneNumberValidationTest extends TestCase
{
    private const PHONE_VALIDATION_RULE = ['phoneNumber' => 'phone:INTERNATIONAL,NL'];

    private static function getPhoneNumbersWithLanguageCode(array $numbers, string $languageCode): array
    {
        return array_map(static fn($value) => [$languageCode, $value], $numbers);
    }

    #[DataProvider('dutchPhoneProvider')]
    #[DataProvider('germanPhoneProvider')]
    #[DataProvider('belgianPhoneProvider')]
    #[DataProvider('internationalPhoneProvider')]
    #[Group('phone')]
    #[TestDox('Test phone number $phoneNumber for country $country should pass==$pass')]
    public function testValidPhoneNumbersAreAccepted(string $country, string $phoneNumber): void
    {
        $this->assertTrue(Validator::make(['phoneNumber' => $phoneNumber], self::PHONE_VALIDATION_RULE)->passes());
    }

    #[DataProvider('invalidDutchPhoneProvider')]
    #[DataProvider('invalidGermanPhoneProvider')]
    #[DataProvider('invalidBelgianPhoneProvider')]
    #[DataProvider('invalidInternationalPhoneProvider')]
    #[Group('phone')]
    #[TestDox('Test phone number $phoneNumber for country $country should pass==$pass')]
    public function testInvalidPhoneNumbersAreRejected(string $country, string $phoneNumber): void
    {
        $this->assertFalse(Validator::make(['phoneNumber' => $phoneNumber], self::PHONE_VALIDATION_RULE)->passes());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function dutchPhoneProvider(): array
    {
        $numbers = [
            '020 234 56 78',
            '0202345678',
            '020-234-56-78',
            '+31 46 4431494',
            '0031 46 4431494',
            '++31 46 4431494',
            '+31 20 234 56 78',
            '+31 20 234 56 78',
            '+31 20 234-56-78',
            '+31 020 234-56-78',
            '06 23 41 56 78',
            '06/23 41 56 78',
            '06-23-41-56-78',
            '+31 06 23 41 56 78',
            '+31 6 23 41 56 78',

        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'NL');
    }

    public static function invalidDutchPhoneProvider(): array
    {
        $numbers = [
            '020234567',
            '+31 6 23 41 56 7',
            '6 23 41 56 7',
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'NL');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function germanPhoneProvider(): array
    {
        $numbers = [
            '+49 (06442) 3933023',
            '+49 (02852) 5996-0',
            '+49 (042) 1818 87 9919',
            '+49 06442 / 3893023',
            '+49 06442 / 38 93 02 3',
            '+49 06442/3839023',
            '+49 042/ 88 17 890 0',
            '+49 221 549144 – 79',
            '+49 221 - 542194 79',
            '+49 (221) - 542944 79',
            '+49 0 52 22 - 9 50 93 10',
            '+49(0)2221-39938-113',
            '+49 0214154914479',
            '+49 02141 54 91 44 79',
            '+4915777953677',
            '+49 015777953677',
            '+49 02162 - 54 91 44 79',
            '+49 (02162) 54 91 44 79',
            '+491739341284',
            '+49 1739341284',
            '+(49) 1739341284',
            '+49 17 39 34 12 84',
            '+49 (1739) 34 12 84',
            '+(49) (1739) 34 12 84',
            '+49 (1739) 34-12-84',
            '+49 1739 34 12 84',
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'DE');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function invalidGermanPhoneProvider(): array
    {
        $numbers = [
            '+49 1739 34 12 8',
            '+49 0151179536779',
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'DE');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function belgianPhoneProvider(): array
    {
        $numbers = [
            '+3212345678',
            '+32 12 34 56 78',
            '+32-12-34-56-78',
            '+32 012 34 56 78',
            '+32 02 341 56 78',
            '+32 0460 41 56 78',
            '+32 460 41 56 78',
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'BE');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function invalidBelgianPhoneProvider(): array
    {
        $numbers = [
            '+32 12 34 56 7',
            '+32 460 41 56 7',
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'BE');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function internationalPhoneProvider(): array
    {
        $numbers = [
            '+44 7911 123456', //UK landline
            '+44 71 11 12 34 56', //UK mobile
            '+33 1 09 75 83 51', //FR landline
            '+33 6 09 75 83 51', //FR mobile
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'INT');
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function invalidInternationalPhoneProvider(): array
    {
        $numbers = [
            '+44 7911 12345', //UK landline
            '+44 71 11 12 34 5', //UK mobile
            '+33 1 09 75 83 5', //FR landline
            '+33 6 09 75 83 5', //FR mobile
        ];
        return self::getPhoneNumbersWithLanguageCode($numbers, 'INT');
    }
}
