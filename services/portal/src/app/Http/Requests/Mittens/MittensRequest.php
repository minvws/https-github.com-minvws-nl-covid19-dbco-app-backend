<?php

declare(strict_types=1);

namespace App\Http\Requests\Mittens;

use App\Helpers\Config;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Webmozart\Assert\Assert;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class MittensRequest
{
    private int $requestCounter = 0;

    public function __construct(
        public readonly string $url,
        public ?array $body = null,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function getJsonBody(): string
    {
        $body = json_encode($this->body, JSON_THROW_ON_ERROR);
        Assert::string($body);
        return $body;
    }

    /**
     * @throws JsonException
     */
    public function toGuzzleRequest(): Request
    {
        return new Request('POST', $this->url, [], $this->getJsonBody());
    }

    public function isRetryAllowed(): bool
    {
        return $this->requestCounter < Config::integer('services.mittens.max_retry_count');
    }

    public function updateRequestCounter(): void
    {
        $this->requestCounter++;
    }
}
