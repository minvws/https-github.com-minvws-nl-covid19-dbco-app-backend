<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Dto\Osiris\Client\PutMessageResult;
use App\Dto\Osiris\Client\SoapMessage;
use App\Exceptions\Osiris\Client\ClientExceptionInterface;

interface OsirisClient
{
    /**
     * @throws ClientExceptionInterface
     */
    public function putMessage(SoapMessage $soapMessage): PutMessageResult;
}
