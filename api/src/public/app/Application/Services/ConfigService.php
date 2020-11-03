<?php
namespace DBCO\PublicAPI\Application\Services;

use DBCO\PublicAPI\Application\Models\Config;
use DBCO\PublicAPI\Application\Repositories\ConfigRepository;
use Psr\Log\LoggerInterface;

/**
 * Responsible for the app config.
 *
 * @package DBCO\PublicAPI\Application\Services
 */
class ConfigService
{
    /**
     * @var ConfigRepository
     */
    private ConfigRepository $configRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param ConfigRepository $configRepository
     * @param LoggerInterface  $logger
     */
    public function __construct(
        ConfigRepository $configRepository,
        LoggerInterface $logger
    )
    {
        $this->configRepository = $configRepository;
        $this->logger = $logger;
    }

    /**
     * Returns the application config.
     *
     * @return Config
     */
    public function getConfig(string $language): Config
    {
        return $this->configRepository->getConfig($language);
    }
}
