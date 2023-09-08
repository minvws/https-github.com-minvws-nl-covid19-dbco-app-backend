<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Exception;

use RuntimeException;

class SearchHashRuntimeException extends RuntimeException implements SearchHashException
{
}
