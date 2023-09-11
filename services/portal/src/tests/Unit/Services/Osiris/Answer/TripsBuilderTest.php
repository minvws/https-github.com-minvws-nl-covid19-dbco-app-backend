<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Trip;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Schema\Types\SchemaType;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\TripsBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;
use function is_numeric;

#[Builder(TripsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class TripsBuilderTest extends TestCase
{
    use AssertAnswers;

    private const CASE_VERSION = 3; // NOTE: you can't change this without changing some assertions below as well!

    public function testCountryMappings(): void
    {
        foreach (Country::all() as $country) {
            $case = $this->createCase(YesNoUnknown::yes(), [self::createTrip([$country])]);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid country mapping for ' . $country->label);
            $this->assertEquals('EPILand1', $answers[0]->code);
            $this->assertTrue(is_numeric($answers[0]->value) || $answers[0]->value === 'KOS');
        }
    }

    public static function tripsProvider(): Generator
    {
        $twoDaysAgo = new CarbonImmutable('2 days ago');
        $tenDaysAgo = new CarbonImmutable('10 days ago');

        yield 'No trips' => [
            YesNoUnknown::yes(),
            [],
            [],
        ];

        yield 'No useful data provided' => [
            YesNoUnknown::yes(),
            [self::createTrip()],
            [],
        ];

        yield 'Single trip, only (single) country provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::deu()]),
            ],
            [
                new Answer('EPILand1', '6029'),
            ],
        ];

        yield 'Single trip, multiple countries provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::deu(), Country::swz()]),
            ],
            [
                new Answer('EPILand1', '6029'),
            ],
        ];

        yield 'Single trip, return date provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip(null, $twoDaysAgo),
            ],
            [
                new Answer('NCOVBtnlndDatTer1', $twoDaysAgo->format('d-m-Y')),
            ],
        ];

        yield 'Single trip, empty country list and return date provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([], $twoDaysAgo),
            ],
            [
                new Answer('NCOVBtnlndDatTer1', $twoDaysAgo->format('d-m-Y')),
            ],
        ];

        yield 'Single trip, country and return date provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::deu()], $twoDaysAgo),
            ],
            [
                new Answer('EPILand1', '6029'),
                new Answer('NCOVBtnlndDatTer1', $twoDaysAgo->format('d-m-Y')),
            ],
        ];


        yield 'Multiple trips, nothing provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip(),
                self::createTrip(),
            ],
            [
                new Answer('MERSPATbuitenl2', 'J'),
            ],
        ];

        yield 'Multiple trips, countries provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::deu()]),
                self::createTrip([Country::swz()]),
            ],
            [
                new Answer('EPILand1', '6029'),
                new Answer('MERSPATbuitenl2', 'J'),
                new Answer('EPILand2', '9036'),
            ],
        ];

        yield 'Multiple trips, country provided for first trip' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::deu()]),
                self::createTrip(),
            ],
            [
                new Answer('EPILand1', '6029'),
                new Answer('MERSPATbuitenl2', 'J'),
            ],
        ];

        yield 'Multiple trips, country provided for second trip' => [
            YesNoUnknown::yes(),
            [
                self::createTrip(),
                self::createTrip([Country::swz()]),
            ],
            [
                new Answer('MERSPATbuitenl2', 'J'),
                new Answer('EPILand2', '9036'),
            ],
        ];

        yield 'Multiple trips, return dates provided' => [
            YesNoUnknown::yes(),
            [
                self::createTrip(null, $tenDaysAgo),
                self::createTrip(null, $twoDaysAgo),
            ],
            [
                new Answer('NCOVBtnlndDatTer1', $tenDaysAgo->format('d-m-Y')),
                new Answer('MERSPATbuitenl2', 'J'),
                new Answer('NCOVBtnlndDatTer2', $twoDaysAgo->format('d-m-Y')),
            ],
        ];

        yield 'Multiple trips, mix countries / dates' => [
            YesNoUnknown::yes(),
            [
                self::createTrip([Country::swz(), Country::deu()]),
                self::createTrip(null, $twoDaysAgo),
            ],
            [
                new Answer('EPILand1', '9036'),
                new Answer('MERSPATbuitenl2', 'J'),
                new Answer('NCOVBtnlndDatTer2', $twoDaysAgo->format('d-m-Y')),
            ],
        ];

        yield 'Multiple trips, but wasAbroad = no' => [
            YesNoUnknown::no(),
            [
                self::createTrip(),
                self::createTrip(),
            ],
            [],
        ];

        yield 'Multiple trips, but wasAbroad = unknown' => [
            YesNoUnknown::unknown(),
            [
                self::createTrip(),
                self::createTrip(),
            ],
            [],
        ];

        yield 'Multiple trips, but wasAbroad = null' => [
            null,
            [
                self::createTrip(),
                self::createTrip(),
            ],
            [],
        ];
    }

    /**
     * @param array<Trip> $trips
     * @param array<Answer> $expectedAnswers
     */
    #[DataProvider('tripsProvider')]
    public function testTrips(?YesNoUnknown $wasAbroad, array $trips, array $expectedAnswers): void
    {
        $case = $this->createCase($wasAbroad, $trips);
        $this->answersForCase($case)->assertAnswers($expectedAnswers);
    }

    public function testMaxTrips(): void
    {
        $trips = [];
        for ($i = 0; $i < 10; $i++) {
            $trips[] = self::createTrip();
        }

        $case = $this->createCase(YesNoUnknown::yes(), $trips);
        $this->answersForCase($case)->assertCount(4); // only MERSPATbuitenlX answers for trip 2...5
    }

    /**
     * @param array<Trip>|null $trips
     */
    private function createCase(?YesNoUnknown $wasAbroad = null, ?array $trips = null): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(self::CASE_VERSION)->newInstance();
        assert($case instanceof CovidCaseV3);
        $case->createdAt = CarbonImmutable::now();
        $case->abroad->wasAbroad = $wasAbroad;
        $case->abroad->trips = $trips;
        return $case;
    }

    /**
     * @param array<Country>|null $countries
     */
    private static function createTrip(
        ?array $countries = null,
        ?DateTimeInterface $returnDate = null,
    ): Trip {
        $trip = EloquentCase::getSchema()
            ->getVersion(self::CASE_VERSION)
            ->getExpectedField('abroad')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getExpectedField('trips')
            ->newInstance();

        assert($trip instanceof Trip);

        $trip->countries = $countries;
        $trip->departureDate = $returnDate !== null ? (new CarbonImmutable($returnDate))->subDays(7) : null;
        $trip->returnDate = $returnDate;

        return $trip;
    }
}
