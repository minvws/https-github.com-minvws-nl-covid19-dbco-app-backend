<?php

declare(strict_types=1);

namespace App\Prometheus\Storage;

use App\Helpers\ArrayReader;
use App\Prometheus\Storage\Redis\Connection;
use App\Prometheus\Storage\Redis\PhpRedis;
use App\Prometheus\Storage\Redis\Predis;
use InvalidArgumentException;
use JsonException;
use Predis\Client;
use Prometheus\Counter;
use Prometheus\Exception\StorageException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Math;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;
use Prometheus\Summary;
use RuntimeException;
use Webmozart\Assert\Assert;

class Redis implements Adapter
{
    public const TYPE_PHPREDIS = 'phpredis';
    public const TYPE_PREDIS   = 'predis';

    private static array $defaultOptions = [
        'type' => self::TYPE_PHPREDIS
    ];

    private const PROMETHEUS_METRIC_KEYS_SUFFIX = '_METRIC_KEYS';

    private static string $prefix = 'PROMETHEUS_';

    private Connection $redis;

    public function __construct(array $options = [], ?Connection $connection = null)
    {
        $options = array_merge(self::$defaultOptions, $options);
        $type = $options['type'] ?? self::TYPE_PHPREDIS;
        unset($options['type']);

        if ($connection === null && $type === self::TYPE_PREDIS) {
            $connection = Predis::create($options);
        } elseif ($connection === null) {
            $connection = PhpRedis::create($options);
        }

        $this->redis = $connection;
    }

    /**
     * @throws StorageException
     */
    public static function forConnection(Connection|\Redis|Client $connection): self
    {
        if ($connection instanceof \Redis) {
            $connection = PhpRedis::forConnection($connection);
        } elseif ($connection instanceof Client) {
            $connection = Predis::forConnection($connection);
        }

        assert($connection instanceof Connection);

        return new self([], $connection);
    }

    /**
     * @throws StorageException
     * @deprecated Use forConnection
     */
    public static function fromExistingConnection(\Redis|Client $redis): self
    {
        return self::forConnection($redis);
    }

    public static function setDefaultOptions(array $options): void
    {
        self::$defaultOptions = array_merge(self::$defaultOptions, $options);
    }

    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }

    /**
     * @deprecated use replacement method wipeStorage from Adapter interface
     * @throws StorageException
     */
    public function flushRedis(): void
    {
        $this->wipeStorage();
    }

    /**
     * @inheritDoc
     */
    public function wipeStorage(): void
    {
        $searchPattern = "";

        $globalPrefix = $this->redis->getPrefix();
        if (is_string($globalPrefix)) {
            $searchPattern .= $globalPrefix;
        }

        $searchPattern .= self::$prefix;
        $searchPattern .= '*';

        $this->redis->eval(
            <<<LUA
local cursor = "0"
repeat
    local results = redis.call('SCAN', cursor, 'MATCH', ARGV[1])
    cursor = results[1]
    for _, key in ipairs(results[2]) do
        redis.call('DEL', key)
    end
until cursor == "0"
LUA
            ,
            [$searchPattern],
        );
    }

    private function metaKey(array $data): string
    {
        return implode(':', [
            $data['name'],
            'meta'
        ]);
    }

    /**
     * @throws StorageException
     */
    private function valueKey(array $data): string
    {
        assert(is_array($data['labelValues']));

        return implode(':', [
            $data['name'],
            $this->encodeLabelValues($data['labelValues']),
            'value'
        ]);
    }

    /**
     * @return MetricFamilySamples[]
     * @throws StorageException
     */
    public function collect(): array
    {
        $metrics = $this->collectHistograms();
        $metrics = array_merge($metrics, $this->collectMetricByType(Gauge::TYPE));
        $metrics = array_merge($metrics, $this->collectMetricByType(Counter::TYPE));
        $metrics = array_merge($metrics, $this->collectSummaries());

        return array_map(
            static function (array $metric): MetricFamilySamples {
                return new MetricFamilySamples($metric);
            },
            $metrics
        );
    }

    /**
     * @throws StorageException
     */
    public function updateHistogram(array $data): void
    {
        assert(is_array($data['buckets']));

        $bucketToIncrease = '+Inf';
        foreach ($data['buckets'] as $bucket) {
            if ($data['value'] <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues']);

        $this->redis->eval(
            <<<LUA
local result = redis.call('hIncrByFloat', KEYS[1], ARGV[1], ARGV[3])
redis.call('hIncrBy', KEYS[1], ARGV[2], 1)
if tonumber(result) >= tonumber(ARGV[3]) then
    redis.call('hSet', KEYS[1], '__meta', ARGV[4])
    redis.call('sAdd', KEYS[2], KEYS[1])
end
return result
LUA
            ,
            [
                $this->toMetricKey($data),
                self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
                $this->jsonEncodeOrStorageException(['b' => 'sum', 'labelValues' => $data['labelValues']]),
                $this->jsonEncodeOrStorageException(['b' => $bucketToIncrease, 'labelValues' => $data['labelValues']]),
                $data['value'],
                $this->jsonEncodeOrStorageException($metaData),
            ],
            2
        );
    }

    /**
     * @throws StorageException
     */
    public function updateSummary(array $data): void
    {
        // store meta
        $summaryKey = self::$prefix . Summary::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX;
        $metaKey = $summaryKey . ':' . $this->metaKey($data);
        $this->redis->setNx($metaKey, $this->jsonEncodeOrStorageException($this->metaData($data)));

        // store value key
        $valueKey = $summaryKey . ':' . $this->valueKey($data);
        $this->redis->setNx($valueKey, $this->jsonEncodeOrStorageException(
            $this->encodeLabelValues(ArrayReader::getArrayByKey($data, 'labelValues')),
        ));

        // trick to handle uniqid collision
        $done = false;
        while (!$done) {
            $sampleKey = $valueKey . ':' . uniqid('', true);
            $done = $this->redis->set($sampleKey, $data['value'], ['NX', 'EX' => $data['maxAgeSeconds']]);
        }
    }

    /**
     * @throws StorageException
     */
    public function updateGauge(array $data): void
    {
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues'], $metaData['command']);
        $this->redis->eval(
            <<<LUA
local result = redis.call(ARGV[1], KEYS[1], ARGV[2], ARGV[3])

if ARGV[1] == 'hSet' then
    if result == 1 then
        redis.call('hSet', KEYS[1], '__meta', ARGV[4])
        redis.call('sAdd', KEYS[2], KEYS[1])
    end
else
    if result == ARGV[3] then
        redis.call('hSet', KEYS[1], '__meta', ARGV[4])
        redis.call('sAdd', KEYS[2], KEYS[1])
    end
end
LUA
            ,
            [
                $this->toMetricKey($data),
                self::$prefix . Gauge::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
                $this->getRedisCommand(ArrayReader::getIntegerByKey($data, 'command')),
                $this->jsonEncodeOrStorageException($data['labelValues']),
                $data['value'],
                $this->jsonEncodeOrStorageException($metaData),
            ],
            2
        );
    }

    /**
     * @throws StorageException
     */
    public function updateCounter(array $data): void
    {
        $metaData = $data;
        unset($metaData['value'], $metaData['labelValues'], $metaData['command']);
        $this->redis->eval(
            <<<LUA
local result = redis.call(ARGV[1], KEYS[1], ARGV[3], ARGV[2])
local added = redis.call('sAdd', KEYS[2], KEYS[1])
if added == 1 then
    redis.call('hMSet', KEYS[1], '__meta', ARGV[4])
end
return result
LUA
            ,
            [
                $this->toMetricKey($data),
                self::$prefix . Counter::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX,
                $this->getRedisCommand(ArrayReader::getIntegerByKey($data, 'command')),
                $data['value'],
                $this->jsonEncodeOrStorageException($data['labelValues']),
                $this->jsonEncodeOrStorageException($metaData),
            ],
            2
        );
    }

    private function metaData(array $data): array
    {
        $metricsMetaData = $data;
        unset($metricsMetaData['value'], $metricsMetaData['command'], $metricsMetaData['labelValues']);

        return $metricsMetaData;
    }

    /**
     * @return array<int, array<string, string>>
     * @throws StorageException
     */
    private function collectHistograms(): array
    {
        $keys = $this->redis->sMembers(self::$prefix . Histogram::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX);
        sort($keys);
        $histograms = [];
        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll($this->removePrefixFromKey($key));
            $histogram = $this->jsonDecodeOrStorageException($raw['__meta']);
            Assert::isArray($histogram);
            unset($raw['__meta']);
            $histogram['samples'] = [];

            // Add the Inf bucket so we can compute it later on
            Assert::keyExists($histogram, 'buckets');
            Assert::isArray($histogram['buckets']);
            $histogram['buckets'][] = '+Inf';

            $allLabelValues = [];
            foreach (array_keys($raw) as $k) {
                $d = $this->jsonDecodeOrStorageException($k);
                Assert::isArray($d);
                Assert::keyExists($d, 'b');

                if ($d['b'] === 'sum') {
                    continue;
                }

                $allLabelValues[] = ArrayReader::getArrayByKey($d, 'labelValues');
            }

            // We need set semantics.
            // This is the equivalent of array_unique but for arrays of arrays.
            $allLabelValues = array_map('unserialize', array_unique(array_map('serialize', $allLabelValues)));
            sort($allLabelValues);

            /** @var array<int, array<string, string>> $labelValues */
            foreach ($allLabelValues as $labelValues) {
                // Fill up all buckets.
                // If the bucket doesn't exist fill in values from
                // the previous one.
                $acc = 0;
                foreach ($histogram['buckets'] as $bucket) {
                    $bucketKey = $this->jsonEncodeOrStorageException(['b' => $bucket, 'labelValues' => $labelValues]);
                    $acc += $raw[$bucketKey] ?? 0;
                    $histogram['samples'][] = [
                        'name' => $histogram['name'] . '_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => array_merge($labelValues, [$bucket]),
                        'value' => $acc,
                    ];
                }

                // Add the count
                $histogram['samples'][] = [
                    'name' => $histogram['name'] . '_count',
                    'labelNames' => [],
                    'labelValues' => $labelValues,
                    'value' => $acc,
                ];

                // Add the sum
                $histogram['samples'][] = [
                    'name' => $histogram['name'] . '_sum',
                    'labelNames' => [],
                    'labelValues' => $labelValues,
                    'value' => $raw[$this->jsonEncodeOrStorageException(['b' => 'sum', 'labelValues' => $labelValues])],
                ];
            }
            $histograms[] = $histogram;
        }
        return $histograms;
    }

    private function removePrefixFromKey(string $key): string
    {
        if ($this->redis->getPrefix() === null) {
            return $key;
        }

        return substr($key, strlen($this->redis->getPrefix()));
    }

    /**
     * @return array<int, array>
     * @throws StorageException
     */
    private function collectSummaries(): array
    {
        $math = new Math();
        $summaryKey = self::$prefix . Summary::TYPE . self::PROMETHEUS_METRIC_KEYS_SUFFIX;
        $keys = $this->redis->keys($summaryKey . ':*:meta');

        $summaries = [];
        foreach ($keys as $metaKeyWithPrefix) {
            $metaKey = $this->removePrefixFromKey($metaKeyWithPrefix);
            $rawSummary = $this->redis->get($metaKey);
            if ($rawSummary === false) {
                continue;
            }
            Assert::stringNotEmpty($rawSummary);
            $summary = $this->jsonDecodeOrStorageException($rawSummary);
            $metaData = $summary;
            $metaDataName = ArrayReader::getStringByKey($metaData, 'name');
            $data = [
                'name' => $metaDataName,
                'help' => ArrayReader::getStringOrNullByKey($metaData, 'help'),
                'type' => ArrayReader::getStringByKey($metaData, 'type'),
                'labelNames' => ArrayReader::getArrayByKey($metaData, 'labelNames'),
                'maxAgeSeconds' => ArrayReader::getIntegerOrNullByKey($metaData, 'maxAgeSeconds'),
                'quantiles' => ArrayReader::getArrayByKey($metaData, 'quantiles'),
                'samples' => [],
            ];

            $values = $this->redis->keys($summaryKey . ':' . $metaDataName . ':*:value');
            foreach ($values as $valueKeyWithPrefix) {
                $valueKey = $this->removePrefixFromKey($valueKeyWithPrefix);
                $rawValue = $this->redis->get($valueKey);
                if ($rawValue === false) {
                    continue;
                }
                Assert::string($rawValue);
                $value = $this->jsonDecodeOrStorageException($rawValue);
                Assert::string($value);
                $encodedLabelValues = $value;
                $decodedLabelValues = $this->decodeLabelValues($encodedLabelValues);

                $samples = [];
                $sampleValues = $this->redis->keys($summaryKey . ':' . $metaDataName . ':' . $encodedLabelValues . ':value:*');
                foreach ($sampleValues as $sampleValueWithPrefix) {
                    /** @var string|int|null $sampleValue */
                    $sampleValue = $this->redis->get($this->removePrefixFromKey($sampleValueWithPrefix));
                    $samples[] = (float) $sampleValue;
                }

                if (count($samples) === 0) {
                    $this->redis->del($valueKey);
                    continue;
                }

                // Compute quantiles
                sort($samples);
                foreach ($data['quantiles'] as $quantile) {
                    $data['samples'][] = [
                        'name' => $metaDataName,
                        'labelNames' => ['quantile'],
                        'labelValues' => array_merge($decodedLabelValues, [$quantile]),
                        'value' => $math->quantile($samples, $quantile),
                    ];
                }

                // Add the count
                $data['samples'][] = [
                    'name' => $metaDataName . '_count',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => count($samples),
                ];

                // Add the sum
                $data['samples'][] = [
                    'name' => $metaDataName . '_sum',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => array_sum($samples),
                ];
            }

            if (count($data['samples']) > 0) {
                $summaries[] = $data;
            } else {
                $this->redis->del($metaKey);
            }
        }
        return $summaries;
    }

    /**
     * @return array<int, array>
     * @throws StorageException
     */
    private function collectMetricByType(string $type): array
    {
        $keys = $this->redis->sMembers(
            sprintf('%s%s%s', self::$prefix, $type, self::PROMETHEUS_METRIC_KEYS_SUFFIX)
        );
        sort($keys);
        $gauges = [];
        foreach ($keys as $key) {
            $raw = $this->redis->hGetAll($this->removePrefixFromKey($key));
            $metric = $this->jsonDecodeOrStorageException($raw['__meta']);
            Assert::isArray($metric);
            unset($raw['__meta']);
            $metric['samples'] = [];
            foreach ($raw as $k => $value) {
                $metric['samples'][] = [
                    'name' => ArrayReader::getStringByKey($metric, 'name'),
                    'labelNames' => [],
                    'labelValues' => $this->jsonDecodeOrStorageException($k),
                    'value' => $value,
                ];
            }
            usort($metric['samples'], function ($a, $b): int {
                return strcmp(
                    implode('', ArrayReader::getArrayByKey($a, 'labelValues')),
                    implode('', ArrayReader::getArrayByKey($b, 'labelValues'))
                );
            });
            $gauges[] = $metric;
        }
        return $gauges;
    }

    private function getRedisCommand(int $cmd): string
    {
        return match ($cmd) {
            Adapter::COMMAND_INCREMENT_INTEGER => 'hIncrBy',
            Adapter::COMMAND_INCREMENT_FLOAT => 'hIncrByFloat',
            Adapter::COMMAND_SET => 'hSet',
            default => throw new InvalidArgumentException('Unknown command'),
        };
    }

    private function toMetricKey(array $data): string
    {
        return implode(':', [self::$prefix, $data['type'], $data['name']]);
    }

    /**
     * @throws StorageException
     */
    private function encodeLabelValues(array $values): string
    {
        return base64_encode($this->jsonEncodeOrStorageException($values));
    }

    /**
     * @throws RuntimeException|StorageException
     */
    private function decodeLabelValues(string $values): array
    {
        $json = base64_decode($values, true);
        if (false === $json) {
            throw new RuntimeException('Cannot base64 decode label values');
        }

        $decodedValues = $this->jsonDecodeOrStorageException($json);
        Assert::isArray($decodedValues);

        return $decodedValues;
    }


    /**
     * @throws StorageException
     */
    private function jsonEncodeOrStorageException(mixed $encodable): string
    {
        try {
            $encoded = json_encode($encodable, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new StorageException($exception->getMessage(), 0, $exception);
        }

        Assert::string($encoded);

        return $encoded;
    }

    /**
     * @throws StorageException
     */
    private function jsonDecodeOrStorageException(string $encodable): mixed
    {
        try {
            $decoded = json_decode($encodable, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new StorageException($exception->getMessage(), 0, $exception);
        }

        return $decoded;
    }
}
