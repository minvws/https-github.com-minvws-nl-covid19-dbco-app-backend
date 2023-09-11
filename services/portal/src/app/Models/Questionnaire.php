<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @deprecated use \App\Models\Eloquent\EloquentQuestionnaire, see DBCO-3004
 */
class Questionnaire
{
    public string $uuid;

    public string $name;

    public string $taskType;

    public string $version;

    /** @var ?array<Question> */
    public ?array $questions = [];
}
