<?php

namespace App\Models;

use Faker\Provider\cs_CZ\DateTime;
use Monolog\DateTimeImmutable;

class Questionnaire
{
    public string $uuid;

    public string $name;

    public string $taskType;

    public string $version;

    public ?array $questions;
}
