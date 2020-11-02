<?php

namespace App\Models;

abstract class Answer
{
    public string $uuid;
    public string $taskUuid;
    public string $questionUuid;

    abstract function progressContribution();
}
