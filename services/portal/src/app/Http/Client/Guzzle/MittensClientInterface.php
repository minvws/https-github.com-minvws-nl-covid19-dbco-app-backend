<?php

declare(strict_types=1);

namespace App\Http\Client\Guzzle;

use App\Http\Requests\Mittens\MittensRequest;

interface MittensClientInterface
{
    /**
     * @throws MittensClientException
     */
    public function post(MittensRequest $mittensRequest): object;
}
