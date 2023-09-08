<?php

declare(strict_types=1);

namespace App\Models\Enums\Osiris;

enum CaseExportType: string
{
    case INITIAL_ANSWERS = 'initial';
    case DEFINITIVE_ANSWERS = 'definitive';
    case DELETED_STATUS = 'deleted';
}
