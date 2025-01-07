<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache;

use DateInterval;
use Kuick\Cache\Serializers\Serializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Kuick\Redis\RedisInterface;
use Psr\SimpleCache\CacheInterface;
use Redis;
use RedisException;

class RedisCache extends AbstractCache implements CacheInterface
{
    private const INFINITE_TTL = 315360000; //10 years

    public function __construct(
        private Redis|RedisInterface $redis,
        private SerializerInterface $serializer = new Serializer(),
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        try {
            $rawData = $this->redis->get($this->sanitizeKey($key));
        } catch (RedisException) {
            throw new CacheException('Redis backend failed during get()');
        }
        if (false === $rawData || null === $rawData) {
            return $default;
        }
        if (!is_string($rawData)) {
            throw new CacheException('Redis backend failed, expected string, got ' . gettype($rawData));
        }
        return $this->serializer->unserialize($rawData);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $sanitizedKey = $this->sanitizeKey($key);
        $ttlSeconds = $this->ttlToInt($ttl);
        try {
            $this->redis->set($sanitizedKey, $this->serializer->serialize($value), $ttlSeconds ? $ttlSeconds : self::INFINITE_TTL);
            //persist item
            if (!$ttlSeconds) {
                $this->redis->persist($sanitizedKey);
            }
        } catch (RedisException) {
            throw new CacheException('Redis backend failed during set()');
        }
        return true;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function has(string $key): bool
    {
        $this->validateKey($key);
        try {
            if (false === $this->redis->exists($this->sanitizeKey($key))) {
                return false;
            }
        } catch (RedisException) {
            throw new CacheException('Redis backend failed during has()');
        }
        return true;
    }

    /**
     * @throws CacheException
     */
    public function delete(string $key): bool
    {
        $this->validateKey($key);
        try {
            $this->redis->del($this->sanitizeKey($key));
        } catch (RedisException) {
            throw new CacheException('Redis backend failed during delete()');
        }
        return true;
    }

    /**
     * @throws CacheException
     */
    public function clear(): bool
    {
        try {
            $this->redis->flushDB();
        } catch (RedisException) {
            throw new CacheException('Redis backend failed during clear()');
        }
        return true;
    }
}
