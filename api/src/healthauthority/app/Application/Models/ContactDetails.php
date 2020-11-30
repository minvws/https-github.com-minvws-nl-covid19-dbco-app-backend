<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Contact details.
 */
class ContactDetails extends AnswerValue
{
    /**
     * @var string|null
     */
    public ?string $firstName;

    /**
     * @var string|null
     */
    public ?string $lastName;

    /**
     * @var string|null
     */
    public ?string $phoneNumber;

    /**
     * @var string|null
     */
    public ?string $email;
}
