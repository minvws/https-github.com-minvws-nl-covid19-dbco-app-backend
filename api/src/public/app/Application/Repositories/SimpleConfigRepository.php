<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\Config;

/**
 * Simple config repository.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class SimpleConfigRepository implements ConfigRepository
{
    /**
     * @inheritDoc
     */
    public function getConfig(string $language): Config
    {
        $config = new Config();

        $config->androidMinimumVersion = 1;
        $config->androidMinimumVersionMessage = 'Please upgrade to the latest store release! (' . $language . ')'; // TODO: translation

        $config->iosMinimumVersion = '1.0.0';
        $config->iosMinimumVersionMessage = 'Please upgrade to the latest store release! (' . $language . ')'; // TODO: translation
        $config->iosAppStoreURL = ''; // TODO: not yet known

        return $config;
    }
}
