<?php

declare(strict_types=1);

namespace App\Prometheus\Storage\Redis;

use Exception;
use Prometheus\Exception\StorageException;

trait HandleException
{
    /**
     * @param callable(): R $closure
     *
     * @return R
     *
     * @throws StorageException
     *
     * @template R
     */
    protected function handleException(callable $closure)
    {
        try {
            return $closure();
        } catch (StorageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
