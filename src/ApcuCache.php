<?php

/**
 * Kuick Cache (https://github.com/milejko/kuick-cache)
 *
 * @link      https://github.com/milejko/kuick-cache
 * @copyright Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache;

use DateInterval;
use Kuick\Cache\Serializers\PhpSerializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Psr\SimpleCache\CacheInterface;

class ApcuCache extends AbstractCache implements CacheInterface
{
    public function __construct(private SerializerInterface $serializer = new PhpSerializer())
    {
        function_exists('apcu_enabled') && apcu_enabled() || throw new CacheException('APCu is not enabled for ' . PHP_SAPI);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $rawData = apcu_fetch($this->sanitizeKey($key));
        if (false === $rawData) {
            return $default;
        }
        if (!is_string($rawData)) {
            throw new CacheException('Redis backend failed, expected string, got ' . gettype($rawData));
        }
        return $this->serializer->unserialize($rawData);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        return (bool) apcu_store($this->sanitizeKey($key), $this->serializer->serialize($value), $this->ttlToInt($ttl));
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);
        return apcu_exists($this->sanitizeKey($key));
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        return (bool) apcu_delete($this->sanitizeKey($key));
    }

    public function clear(): bool
    {
        return (bool) apcu_clear_cache();
    }
}
