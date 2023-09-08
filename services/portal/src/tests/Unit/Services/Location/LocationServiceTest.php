<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Location;

use App\Services\Location\Dto\Location;
use App\Services\Location\LocationClient;
use App\Services\Location\LocationService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function app;
use function json_encode;
use function range;

#[Group('guzzle')]
class LocationServiceTest extends TestCase
{
    private MockHandler $mockHandler;

    private LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $this->app->instance(LocationClient::class, new LocationClient([
            'handler' => HandlerStack::create($this->mockHandler),
        ]));

        $this->service = app(LocationService::class);
    }

    public function testFindByQuery(): void
    {
        $this->mockSuccessfulResponseMultipleLocations();

        $collection = $this->service->findByQuery('Straat 1234HD 99, Duckstad');

        $this->assertCount(1, $collection);

        /** @var Location $location */
        $location = $collection->first();

        $this->assertEquals('b7f5e8308ccab144a10f7e46b55c2791', $location->id);
        $this->assertEquals('De Kluis', $location->name);
        $this->assertEquals('Euroweg', $location->street);
        $this->assertEquals('1', $location->houseNumber);
        $this->assertNull($location->houseNumberAddition);
        $this->assertEquals('Duckstad', $location->town);
        $this->assertNull($location->country);
        $this->assertEquals('1234HD', $location->postalCode);
        $this->assertEquals('adres', $location->type);
        $this->assertEquals(['google_places'], $location->sources);
        $this->assertFalse($location->business);
        $this->assertEquals('088 5555555', $location->phone);
        $this->assertEquals('some@example.com', $location->email);
        $this->assertEquals('https://example.com/', $location->url);
        $this->assertNull($location->kvk);
        $this->assertEquals('GGD Regio Utrecht', $location->ggdName);
        $this->assertEquals('GG2511', $location->ggdCode);
        $this->assertNull($location->ggdTown);
        $this->assertNull($location->ggdMunicipality);
        $this->assertEquals(52.29_179, $location->latitude);
        $this->assertEquals(5.9_074_568, $location->longitude);

        $this->assertEquals('1', $location->completeHouseNumber());
        $this->assertEquals('Euroweg 1, 1234HD Duckstad', $location->addressLabel());
    }

    public function testFindByQueryMultipleResults(): void
    {
        $this->mockSuccessfulResponseMultipleLocations(3);

        $collection = $this->service->findByQuery('Straat 1234HD 99, Duckstad');

        $this->assertCount(3, $collection);
    }

    public function testFindByQueryEmptyResponse(): void
    {
        $this->mockEmptyResponse();

        $collection = $this->service->findByQuery('Straat 1234HD 99, Duckstad');

        $this->assertCount(0, $collection);
    }

    public function testFindForQueryId(): void
    {
        $this->mockSuccessfulResponseSingleLocation();

        $location = $this->service->findForQueryId(1);

        $this->assertInstanceOf(Location::class, $location);
    }

    public function testFindForPostalCode(): void
    {
        $this->mockSuccessfulResponseMultipleLocations();

        $collection = $this->service->findForPostalCode('1234AA');

        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Location::class, $collection->first());
    }

    public function testFindForLocationId(): void
    {
        $this->mockSuccessfulResponseSingleLocation();

        $location = $this->service->findForLocationId('b7f5e8308ccab144a10f7e46b55c2791');

        $this->assertInstanceOf(Location::class, $location);
    }

    private function mockSuccessfulResponseMultipleLocations(int $count = 1): void
    {
        $locations = [];

        foreach (range(1, $count) as $index) {
            $locations[] = $this->getSampleLocation($index);
        }

        $this->mockHandler->append(new GuzzleResponse(Response::HTTP_OK, [], json_encode([
            'locations' => $locations,
        ])));
    }

    private function mockSuccessfulResponseSingleLocation(): void
    {
        $this->mockHandler->append(new GuzzleResponse(Response::HTTP_OK, [], json_encode(
            $this->getSampleLocation(),
        )));
    }

    private function mockEmptyResponse(): void
    {
        $this->mockHandler->append(new GuzzleResponse(Response::HTTP_OK, [], json_encode([
            'locations' => [],
        ])));
    }

    private function getSampleLocation(int $index = 1): array
    {
        return [
            'bag_id' => 12_312_412 . $index,
            'business' => false,
            'city' => 'Duckstad',
            'contact' => [
                'email' => 'some@example.com',
                'kvk' => null,
                'tel' => '088 5555555',
                'url' => 'https://example.com/',
            ],
            'geo' => [
                'lat' => 52.29_179,
                'lon' => 5.9_074_568,
            ],
            'ggd' => [
                'city' => null,
                'code' => 'GG251' . $index,
                'municipality' => null,
                'name' => 'GGD Regio Utrecht',
            ],
            'house_number' => $index,
            'house_number_extension' => null,
            'id' => 'b7f5e8308ccab144a10f7e46b55c279' . $index,
            'location_name' => 'De Kluis',
            'meta' => [
                'google_places_id' => 'ChIJ71Bj1PxGxkcRNu_IaPbn_hI',
            ],
            'sources' => [
                'google_places',
            ],
            'street_name' => 'Euroweg',
            'country' => null,
            'type' => 'adres',
            'zipcode' => '1234HD',
        ];
    }
}
