<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Full contact details.
 */
class ContactDetailsFull extends ContactDetails
{
    /**
     * @var string|null
     */
    public ?string $address1;

    /**
     * @var string|null
     */
    public ?string $houseNumber;

    /**
     * @var string|null
     */
    public ?string $address2;

    /**
     * @var string|null
     */
    public ?string $zipcode;

    /**
     * @var string|null
     */
    public ?string $city;
}
