<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Full contact details.
 */
class ContactDetailsFull extends ContactDetails
{
    /**
     * @var string
     */
    public string $address1;

    /**
     * @var string
     */
    public string $houseNumber;

    /**
     * @var string
     */
    public string $address2;

    /**
     * @var string
     */
    public string $zipcode;

    /**
     * @var string
     */
    public string $city;
}
