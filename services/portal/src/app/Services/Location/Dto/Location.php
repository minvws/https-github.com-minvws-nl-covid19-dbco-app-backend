<?php

declare(strict_types=1);

namespace App\Services\Location\Dto;

use App\Helpers\PostalCodeHelper;
use Illuminate\Contracts\Support\Arrayable;

use function sprintf;
use function trim;
use function ucfirst;

class Location implements Arrayable
{
    public string $id;

    public ?string $name;

    public ?string $street;

    public ?string $houseNumber;

    public ?string $houseNumberAddition;

    public ?string $town;

    public ?string $country;

    public ?string $postalCode;

    public ?string $type;

    public array $sources = [];

    public bool $business = false;

    public ?string $phone;

    public ?string $email;

    public ?string $url;

    public ?string $kvk;

    public ?string $ggdName;

    public ?string $ggdCode;

    public ?string $ggdTown;

    public ?string $ggdMunicipality;

    public ?float $latitude;

    public ?float $longitude;

    public function toArray(): array
    {
        return (array) $this;
    }

    public function addressLabel(): string
    {
        return trim(sprintf(
            '%s %s, %s %s',
            $this->street,
            $this->completeHouseNumber(),
            $this->normalizePostalCode($this->postalCode),
            $this->town,
        ));
    }

    public function completeHouseNumber(): string
    {
        if ($this->houseNumber === null) {
            return $this->houseNumberAddition ?? '';
        }

        return trim($this->houseNumber . ' ' . ($this->houseNumberAddition ?? ''));
    }

    public function toResult(): array
    {
        $label = '';
        if ($this->name !== null && $this->name !== '') {
            $label = $this->name;
        }
        if ($label === '' && $this->type) {
            $label = $this->type;
        }

        return [
            'id' => $this->id,
            'label' => ucfirst($label),
            'indexCount' => 0,
            'category' => null,
            'addressLabel' => $this->addressLabel(),
            'address' => [
                'street' => $this->street,
                'houseNumber' => $this->houseNumber,
                'houseNumberSuffix' => $this->houseNumberAddition,
                'postalCode' => $this->normalizePostalCode($this->postalCode),
                'town' => $this->town,
            ],
            'ggd' => [
                'code' => $this->ggdCode,
                'municipality' => $this->ggdMunicipality,
            ],
        ];
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        if ($postalCode !== null) {
            return PostalCodeHelper::normalize($postalCode);
        }

        return null;
    }
}
