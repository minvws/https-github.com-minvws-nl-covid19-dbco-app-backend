<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsFull as ContactDetailsFullModel;
use stdClass;

/**
 * Contact details full DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class ContactDetailsFull
{
    /**
     * @var ContactDetailsFullModel $contactDetailsFull
     */
    private ContactDetailsFullModel $contactDetailsFull;

    /**
     * Constructor.
     *
     * @param ContactDetailsFullModel $contactDetailsFull
     */
    public function __construct(ContactDetailsFullModel $contactDetailsFull)
    {
        $this->contactDetailsFull = $contactDetailsFull;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return ContactDetailsFullModel
     */
    public static function jsonUnserialize(stdClass $data): ContactDetailsFullModel
    {
        $contactDetailsFull = new ContactDetailsFullModel();
        $contactDetailsFull->firstName = $data->firstName ?? '';
        $contactDetailsFull->lastName = $data->lastName ?? '';
        $contactDetailsFull->phoneNumber = $data->phoneNumber ?? '';
        $contactDetailsFull->email = $data->email ?? '';
        $contactDetailsFull->address1 = $data->address1 ?? '';
        $contactDetailsFull->houseNumber = $data->houseNumber ?? '';
        $contactDetailsFull->address2 = $data->address2 ?? '';
        $contactDetailsFull->zipcode = $data->zipcode ?? '';
        $contactDetailsFull->city = $data->city ?? '';
        return $contactDetailsFull;
    }
}
