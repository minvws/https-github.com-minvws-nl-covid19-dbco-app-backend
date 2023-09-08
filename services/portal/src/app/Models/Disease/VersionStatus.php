<?php

declare(strict_types=1);

namespace App\Models\Disease;

enum VersionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
