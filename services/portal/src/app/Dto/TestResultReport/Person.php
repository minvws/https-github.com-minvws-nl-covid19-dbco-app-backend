<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

final class Person
{
    public ?string $initials;
    public ?string $firstName;
    public string $surname;
    public ?string $bsn;
    public DateTimeInterface $dateOfBirth;
    public Gender $gender;
    public ?string $email;
    public ?string $telephoneNumber;
    public Address $address;

    private function __construct()
    {
    }

    public static function fromArray(array $array): self
    {
        $self = new Person();

        $self->initials = $array['initials'];
        $self->firstName = $array['firstName'];
        $self->surname = $array['surname'];
        $self->bsn = $array['bsn'];

        $dateOfBirth = DateTimeImmutable::createFromFormat('m-d-Y', $array['dateOfBirth']);
        $self->dateOfBirth = $dateOfBirth !== false ? $dateOfBirth :
            throw new RunTimeException('Failed to parse dateOfBirth');

        $self->gender = new Gender($array['gender']);
        $self->email = $array['email'];
        $self->telephoneNumber = $array['telephoneNumber'];
        $self->address = Address::fromArray($array['address']);

        return $self;
    }
}
