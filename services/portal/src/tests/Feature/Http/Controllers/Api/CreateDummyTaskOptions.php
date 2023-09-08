<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\CarbonInterface;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\TaskGroup;

final class CreateDummyTaskOptions
{
    public ContactCategory $category;
    public ?CarbonInterface $dateOfLastExposure = null;
    public ?TaskGroup $taskGroup = null;

    /*
     * Task fields (filled by BCOer)
     */
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $context = null;

    /*
     * Questionnaire fields (filled by index)
     */
    public ?string $questionnaireEmail = null;
    public ?CarbonInterface $questionnaireDateOfBirth = null;
    public ?string $questionnairePhone = null;
    public ?string $questionnaireFirstname = null;
    public ?string $questionnaireLastname = null;

    public function __construct()
    {
        $this->category = ContactCategory::cat1();
    }

    public static function none(): CreateDummyTaskOptions
    {
        return new CreateDummyTaskOptions();
    }
}
