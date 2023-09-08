<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Exception;

use RuntimeException;

class SlotInvalidException extends RuntimeException implements SearchHashException
{
}
