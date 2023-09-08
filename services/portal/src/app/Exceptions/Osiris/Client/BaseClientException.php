<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\Client;

use Exception;

abstract class BaseClientException extends Exception implements ClientExceptionInterface
{
}
