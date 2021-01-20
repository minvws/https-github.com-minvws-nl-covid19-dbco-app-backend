<?php
namespace DBCO\PublicAPI\Application\Models;

/**
 * App configuration.
 */
class Config
{
    public int $androidMinimumVersion;
    public string $androidMinimumVersionMessage;

    public string $iosMinimumVersion;
    public string $iosMinimumVersionMessage;
    public string $iosAppStoreURL;

    public array $featureFlags;
}
