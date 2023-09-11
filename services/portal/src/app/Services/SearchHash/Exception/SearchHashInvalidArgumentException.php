<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Exception;

use InvalidArgumentException;

class SearchHashInvalidArgumentException extends InvalidArgumentException implements SearchHashException
{
}
