<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Contact details.
 */
class ContactDetailsQuestion extends Question
{
    /**
     * @var string
     */
    public string $questionType = 'contactdetails';
}
