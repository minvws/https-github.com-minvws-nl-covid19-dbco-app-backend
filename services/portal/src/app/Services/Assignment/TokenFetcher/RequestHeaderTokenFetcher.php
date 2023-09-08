<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenFetcher;

use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;

use function assert;
use function is_string;
use function sprintf;

final class RequestHeaderTokenFetcher implements TokenFetcher
{
    private readonly string $headerName;

    public function __construct(private readonly Request $request, Config $config)
    {
        $headerName = $config->get('assignment.token_fetcher.request_header.header_name');

        assert(is_string($headerName), AssignmentInvalidValueException::wrongType('headerName', 'string', $headerName));

        $this->headerName = $headerName;
    }

    public function hasToken(): bool
    {
        return $this->request->hasHeader($this->headerName) && is_string($this->request->header($this->headerName));
    }

    public function getToken(): string
    {
        $header = $this->request->header($this->headerName);

        if (!$this->hasToken()) {
            throw new AssignmentRuntimeException(sprintf('Failed fetching token header "%s".', $this->headerName));
        }

        assert(is_string($header));

        return $header;
    }
}
