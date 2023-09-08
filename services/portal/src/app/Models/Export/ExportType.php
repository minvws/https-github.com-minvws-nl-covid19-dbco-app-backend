<?php

declare(strict_types=1);

namespace App\Models\Export;

enum ExportType: string
{
    case Case_ = 'case'; // https://github.com/pdepend/pdepend/issues/640
    case Place = 'place';
    case Event = 'event';
}
