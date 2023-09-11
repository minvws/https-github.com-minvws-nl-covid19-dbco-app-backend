<?php

declare(strict_types=1);

namespace App\Repositories\Osiris;

use stdClass;

use function config;
use function rand;
use function simplexml_load_file;

/**
 * @codeCoverageIgnore
 */
final class OsirisMockRepository
{
    public function putMessage(mixed $parameters): object
    {
        $file = match (config('services.osiris.mock_client_response')) {
            'error' => 'PutMessageFailedResponse.xml',
            'warning' => 'PutMessageSuccessWithWarningsResponse.xml',
            default => 'PutMessageSuccessResponse.xml',
        };

        $putMessageResult = simplexml_load_file(__DIR__ . '/responses/' . $file);
        if (empty($putMessageResult) || empty($putMessageResult->resultaat)) {
            return new stdClass();
        }

        $putMessageResult->resultaat->osiris_nummer = rand(10_000, 99_999);

        $response = new stdClass();
        $response->PutMessageResult = $putMessageResult->asXML();

        return $response;
    }

    public function getLastResponse(): string
    {
        return '';
    }
}
