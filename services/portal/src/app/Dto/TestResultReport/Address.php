<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

final class Address
{
    public ?string $streetName;
    public ?string $houseNumber;
    public ?string $houseNumberSuffix;
    public string $postcode;
    public ?string $city;

    private function __construct()
    {
    }

    public static function fromArray(array $array): self
    {
        $self = new Address();

        $self->streetName = $array['streetName'];
        $self->houseNumber = $array['houseNumber'];
        $self->houseNumberSuffix = $array['houseNumberSuffix'];
        $self->postcode = $array['postcode'];
        $self->city = $array['city'];

        return $self;
    }
}
