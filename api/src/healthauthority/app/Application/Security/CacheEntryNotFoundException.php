<?php
namespace DBCO\HealthAuthorityAPI\Application\Security;

/**
 * Thrown when a certain key / identifier doesn't exist in the security cache.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class CacheEntryNotFoundException extends \RuntimeException
{
}