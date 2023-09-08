<?php

declare(strict_types=1);

namespace App\Models\Enums\Api\Export;

enum ExportApiParameter : string
{
    case CURSOR = 'cursor';
    case SINCE_PARAMETER = 'sinceParameter';
}
