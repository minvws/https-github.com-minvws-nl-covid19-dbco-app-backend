<?php
namespace App\Security;

/**
 * Thrown when a certain key / identifier doesn't exist in the security cache.
 *
 * @package App\Security
 */
class CacheEntryNotFoundException extends \RuntimeException
{
}
