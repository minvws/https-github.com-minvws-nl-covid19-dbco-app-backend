<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\Config;

/**
 * Used for retrieving the app config
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
interface ConfigRepository
{
    /**
     * Returns the app config.
     *
     * @param string $language Language.
     *
     * @return Config
     */
    public function getConfig(string $language): Config;
}
