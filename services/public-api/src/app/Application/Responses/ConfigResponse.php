<?php
namespace DBCO\PublicAPI\Application\Responses;

use DBCO\PublicAPI\Application\Models\Config;
use DBCO\Shared\Application\Responses\Response;

/**
 * Proxy response.
 */
class ConfigResponse extends Response implements \JsonSerializable
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Constructor.
     *
     * @param ProxyData $data Data.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this->config);
    }
}
