<?php

declare(strict_types=1);

namespace App\Models\Disease;

enum Entity: string
{
    case Dossier = 'dossier';
    case Contact = 'contact';
    case Event = 'event';
}
