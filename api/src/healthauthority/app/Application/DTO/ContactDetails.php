<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\ContactDetails as ContactDetailsModel;
use stdClass;

/**
 * Contact details DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class ContactDetails
{
    /**
     * @var ContactDetailsModel $contactDetails
     */
    private ContactDetailsModel $contactDetails;

    /**
     * Constructor.
     *
     * @param ContactDetailsModel $contactDetails
     */
    public function __construct(ContactDetailsModel $contactDetails)
    {
        $this->contactDetails = $contactDetails;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return ContactDetailsModel
     */
    public static function jsonUnserialize(stdClass $data): ContactDetailsModel
    {
        $contactDetails = new ContactDetailsModel();
        $contactDetails->firstName = $data->firstName ?? '';
        $contactDetails->lastName = $data->lastName ?? '';
        $contactDetails->phoneNumber = $data->phoneNumber ?? '';
        $contactDetails->email = $data->email ?? '';
        return $contactDetails;
    }
}
