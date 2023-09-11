<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\Config;
use Illuminate\Http\Middleware\TrustHosts as IlluminateTrustHosts;

use function array_filter;
use function parse_url;
use function preg_quote;

use const PHP_URL_HOST;

class TrustHosts extends IlluminateTrustHosts
{
    /**
     * Get the host patterns that should be trusted.
     */
    public function hosts(): array
    {
        $trustedHosts = [];
        $trustedHosts[] = $this->allSubdomainsOfApplicationUrl();

        $trustedHostsConfig = Config::array('security.trustedHosts');
        foreach ($trustedHostsConfig as $trustedHost) {
            $trustedHosts[] = $this->allSubdomainsOfUrl($trustedHost);
        }

        return array_filter($trustedHosts);
    }

    private function allSubdomainsOfUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host) {
            return '^(.+\.)?' . preg_quote($host) . '$';
        }

        return null;
    }
}
