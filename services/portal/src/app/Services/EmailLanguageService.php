<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use MinVWS\DBCO\Enum\Models\EmailLanguage;

class EmailLanguageService
{
    public function getByCase(EloquentCase $eloquentCase): EmailLanguage
    {
        $alternativeEmailLanguage = $eloquentCase->alternativeLanguage->emailLanguage;

        if ($alternativeEmailLanguage === null) {
            return EmailLanguage::nl();
        }

        return $alternativeEmailLanguage;
    }

    public function getByTask(EloquentTask $eloquentTask): EmailLanguage
    {
        $alternativeEmailLanguage = $eloquentTask->alternativeLanguage->emailLanguage;

        if ($alternativeEmailLanguage === null) {
            return EmailLanguage::nl();
        }

        return $alternativeEmailLanguage;
    }

    public function getByCaseOrTask(EloquentCase $eloquentCase, ?EloquentTask $eloquentTask): EmailLanguage
    {
        return $eloquentTask !== null ? $this->getByTask($eloquentTask) : $this->getByCase($eloquentCase);
    }
}
