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
        $upgradeMessage = [
            'nl_NL' => 'Om de app te gebruiken heb je de laatste versie uit de store nodig.',
        ];

        if (!isset($upgradeMessage[$language])) {
            $language = 'nl_NL';
        }

        $config = new Config();

        $config->androidMinimumVersion = 1;
        $config->androidMinimumVersionMessage = $upgradeMessage[$language];

        $config->iosMinimumVersion = '1.0.0';
        $config->iosMinimumVersionMessage = $upgradeMessage[$language];
        $config->iosAppStoreURL = 'https://apps.apple.com/nl/app/id1533805739';

        $config->featureFlags = [
            'enableContactCalling' => true,
            'enablePerspectiveSharing' => false,
            'enablePerspectiveCopy' => true
        ];

        return $config;
    }
}
