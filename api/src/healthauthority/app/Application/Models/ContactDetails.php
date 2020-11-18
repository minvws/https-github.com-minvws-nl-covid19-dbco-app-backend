<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Contact details.
 */
class ContactDetails extends AnswerValue
{
    /**
     * @var string
     */
    public string $firstName;

    /**
     * @var string
     */
    public string $lastName;

    /**
     * @var string
     */
    public string $phoneNumber;

    /**
     * @var string
     */
    public string $email;
}
