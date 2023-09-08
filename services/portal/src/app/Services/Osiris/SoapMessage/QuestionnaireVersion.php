<?php

declare(strict_types=1);

namespace App\Services\Osiris\SoapMessage;

enum QuestionnaireVersion: int
{
    case V9 = 9;
    case V10 = 10;
}
