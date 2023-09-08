<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use App\Helpers\Config;
use Webmozart\Assert\Assert;

final class IdentityHubProviderConfigDto
{
    public function __construct(
        public readonly string $revokeUrl,
        public readonly string $tokenUrl,
        public readonly string $authUrl,
        public readonly string $userUrl,
        public readonly string $claimsVrRegioCode,
        public readonly string $claimsDepartment,
    ) {
    }

    public static function fromConfig(string $configKey = 'services.identityhub'): self
    {
        $config = Config::array($configKey);

        Assert::string($config['revokeUrl'] ?? null);
        Assert::string($config['tokenUrl'] ?? null);
        Assert::string($config['authUrl'] ?? null);
        Assert::string($config['userUrl'] ?? null);
        Assert::string($config['claims']['department'] ?? null);
        Assert::string($config['claims']['vrRegioCode'] ?? null);

        return new IdentityHubProviderConfigDto(
            revokeUrl: $config['revokeUrl'],
            tokenUrl: $config['tokenUrl'],
            authUrl: $config['authUrl'],
            userUrl: $config['userUrl'],
            claimsVrRegioCode: $config['claims']['vrRegioCode'],
            claimsDepartment: $config['claims']['department'],
        );
    }
}
