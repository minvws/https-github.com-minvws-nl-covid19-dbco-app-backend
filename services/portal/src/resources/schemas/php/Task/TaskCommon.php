<?php

namespace App\Models\Versions\Task;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property string $uuid
 * @property ?string $pseudoId
 * @property \MinVWS\DBCO\Enum\Models\TaskGroup $taskGroup
 * @property \App\Models\Versions\Task\General\GeneralV1 $general
 * @property \App\Models\Versions\Task\Job\JobV1 $job
 * @property \App\Models\Versions\Task\Symptoms\SymptomsV1 $symptoms
 * @property \App\Models\Versions\Task\Circumstances\CircumstancesV1 $circumstances
 * @property \App\Models\Versions\Task\AlternateContact\AlternateContactV1 $alternateContact
 * @property \App\Models\Versions\Task\AlternativeLanguage\AlternativeLanguageV1 $alternativeLanguage
 */
interface TaskCommon
{
}

