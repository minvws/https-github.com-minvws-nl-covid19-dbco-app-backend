<?php

declare(strict_types=1);

namespace Tests\Feature\Services\SearchHash\EloquentCase\Index;

use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use Closure;
use Faker\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function str_repeat;

#[Group('search-hash')]
class IndexHashTest extends FeatureTestCase
{
    /**
     * @param Closure(Index):void $indexClosure
     */
    #[DataProvider('fromIndexData')]
    public function testFromIndex(Closure $indexClosure, IndexHash $expectedIndexHash): void
    {
        $this->assertEquals($expectedIndexHash, IndexHash::fromIndex(Index::newInstanceWithVersion(1, $indexClosure)));
    }

    public static function fromIndexdata(): array
    {
        $faker = Factory::create('nl_NL');

        $dateOfBirth = $faker->dateTimeBetween();
        $lastname = $faker->lastName();
        $postalCode = $faker->postcode();
        $houseNumber = (string) $faker->numberBetween(10, 100);
        $lastThreeDigitsBsn = (string) $faker->numberBetween(100, 999);
        $bsnCensored = str_repeat('*', $faker->numberBetween(5, 6)) . $lastThreeDigitsBsn;

        return [
            'all data except houseNumberSuffix' => [
                'indexClosure' => static function (Index $index) use ($dateOfBirth, $lastname, $houseNumber, $postalCode, $bsnCensored): void {
                    $index->dateOfBirth = $dateOfBirth;
                    $index->lastname = $lastname;
                    $index->bsnCensored = $bsnCensored;
                    $index->address = IndexAddress::newInstanceWithVersion(
                        1,
                        static function (IndexAddress $address) use ($postalCode, $houseNumber): void {
                            $address->postalCode = $postalCode;
                            $address->houseNumber = $houseNumber;
                        },
                    );
                },
                'expectedIndexHash' => new IndexHash(
                    dateOfBirth: $dateOfBirth,
                    lastname: $lastname,
                    lastThreeBsnDigits: $lastThreeDigitsBsn,
                    postalCode: $postalCode,
                    houseNumber: $houseNumber,
                    houseNumberSuffix: null,
                ),
            ],
            'only lastname and bsnCensored' => [
                'indexClosure' => static function (Index $index) use ($lastname, $bsnCensored): void {
                    $index->lastname = $lastname;
                    $index->bsnCensored = $bsnCensored;
                },
                'expectedIndexHash' => new IndexHash(
                    dateOfBirth: null,
                    lastname: $lastname,
                    lastThreeBsnDigits: $lastThreeDigitsBsn,
                    postalCode: null,
                    houseNumber: null,
                    houseNumberSuffix: null,
                ),
            ],
        ];
    }
}
