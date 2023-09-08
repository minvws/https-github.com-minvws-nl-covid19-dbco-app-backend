<?php

declare(strict_types=1);

namespace App\Prometheus\Storage\Redis;

use Prometheus\Exception\StorageException;

interface Connection
{
    public function getPrefix(): ?string;

    /**
     * @throws StorageException
     * @return mixed
     */
    public function eval(string $script, array $args = [], int $numOfKeys = 0);

    /**
     * @throws StorageException
     */
    public function setNx(string $key, string $value): bool;

    /**
     * @param mixed          $value
     * @param int|array|null $timeout
     *
     * @throws StorageException
     */
    public function set(string $key, $value, $timeout = null): bool;

    /**
     * @throws StorageException
     */
    public function sMembers(string $key): array;

    /**
     * @throws StorageException
     */
    public function hGetAll(string $key): array;

    /**
     * @throws StorageException
     */
    public function keys(string $pattern): array;

    /**
     * @return mixed|null
     *
     * @throws StorageException
     */
    public function get(string $key);

    /**
     * @throws StorageException
     */
    public function del(string ... $keyOrKeys): int;
}
