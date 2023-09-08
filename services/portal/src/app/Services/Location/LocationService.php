<?php

declare(strict_types=1);

namespace App\Services\Location;

use App\Exceptions\LocationApiUnauthenticatedException;
use App\Services\Location\Dto\Location;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

use function collect;
use function config;
use function count;
use function in_array;
use function is_array;
use function json_decode;
use function sprintf;

readonly class LocationService
{
    public function __construct(
        private LocationClient $client,
    ) {
    }

    /**
     * @param array<string> $filterIds
     *
     * @return Collection<int, Location>
     *
     * @throws LocationApiUnauthenticatedException
     */
    public function findByQuery(string $query, array $filterIds = []): Collection
    {
        $data = $this->doRequest('/locations', ['location' => $query]);
        $locations = $this->parseData($data);

        return $locations->filter(static function (Location $location) use ($filterIds) {
            return !in_array($location->id, $filterIds, true);
        });
    }

    public function findForQueryId(int $queryId): ?Location
    {
        $data = $this->doRequest(sprintf('/locations/%d', $queryId));

        if (empty($data)) {
            return null;
        }

        return $this->parseSingleLocation($data);
    }

    public function findForPostalCode(string $postalCode): Collection
    {
        $data = $this->doRequest('/location', ['address' => $postalCode]);

        return $this->parseData($data);
    }

    public function findForLocationId(string $locationId): ?Location
    {
        $data = $this->doRequest(sprintf('/location/%s', $locationId));

        if (empty($data)) {
            return null;
        }

        return $this->parseSingleLocation($data);
    }

    /**
     * @return Collection<int, Location>
     */
    private function parseData(array $data = []): Collection
    {
        $collection = collect();

        if (!isset($data['locations']) || !is_array($data['locations']) || count($data['locations']) === 0) {
            return $collection;
        }

        foreach ($data['locations'] as $location) {
            $collection->push($this->parseSingleLocation($location));
        }

        return $collection;
    }

    private function parseSingleLocation(array $data = []): Location
    {
        $location = new Location();
        $location->id = $data['id'];
        $location->name = $data['location_name'] ?? null;
        $location->street = $data['street_name'] ?? null;
        $location->houseNumber = isset($data['house_number']) ? (string) $data['house_number'] : null;
        $location->houseNumberAddition = $data['house_number_extension'] ?? null;
        $location->town = $data['city'] ?? null;
        $location->country = $data['country'] ?? null;
        $location->postalCode = $data['zipcode'] ?? null;
        $location->type = $data['type'] ?? null;
        $location->sources = $data['sources'] ?? [];
        $location->business = $data['business'] ?? false;
        $location->phone = $data['contact']['tel'] ?? null;
        $location->email = $data['contact']['email'] ?? null;
        $location->url = $data['contact']['url'] ?? null;
        $location->kvk = $data['contact']['kvk'] ?? null;
        $location->ggdName = $data['ggd']['name'] ?? null;
        $location->ggdCode = $data['ggd']['code'] ?? null;
        $location->ggdTown = $data['ggd']['city'] ?? null;
        $location->ggdMunicipality = $data['ggd']['municipality'] ?? null;
        $location->latitude = $data['geo']['lat'] ?? null;
        $location->longitude = $data['geo']['lon'] ?? null;

        return $location;
    }

    private function doRequest(string $url, array $query = []): array
    {
        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'query' => $query,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Api-Key' => config('services.location.api_key'),
                        'Content-Type' => 'application/json',
                    ],
                ],
            );

            $json = (string) $response->getBody();
            if ($json === '') {
                return [];
            }

            return json_decode($json, true);
        } catch (GuzzleException $exception) {
            if ($exception->getCode() === Response::HTTP_UNAUTHORIZED) {
                throw new LocationApiUnauthenticatedException('Location API unauthorized');
            }

            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return [];
            }

            return [];
        }
    }
}
