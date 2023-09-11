<?php

namespace MinVWS\DBCO\Metrics\Repositories;

interface TaskProgressRepository
{
    public function getTaskData(string $taskUuid): array;
}
