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
use Kuick\Cache\Serializers\PhpSerializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Psr\SimpleCache\CacheInterface;

class InMemoryCache extends AbstractCache implements CacheInterface
{
    /**
     * @var array<string, string>
     */
    private array $store = [];

    /**
     * @var array<string, ?int>
     */
    private array $ttls = [];

    public function __construct(
        private SerializerInterface $serializer = new PhpSerializer(),
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        return $this->has($key) ?
            $this->serializer->unserialize($this->store[$this->sanitizeKey($key)]) :
            $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $sanitizedKey = $this->sanitizeKey($key);
        $intTtl = $this->ttlToInt($ttl);
        $this->store[$sanitizedKey] = $this->serializer->serialize($value);
        if ($intTtl) {
            $this->ttls[$sanitizedKey] = time() + $intTtl;
        }
        return true;
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);
        $sanitizedKey = $this->sanitizeKey($key);
        if (!array_key_exists($sanitizedKey, $this->store)) {
            return false;
        }
        if (isset($this->ttls[$sanitizedKey]) && $this->ttls[$sanitizedKey] <= time()) {
            unset($this->store[$sanitizedKey], $this->ttls[$sanitizedKey]);
            return false;
        }
        return true;
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $sanitizedKey = $this->sanitizeKey($key);
        unset($this->store[$sanitizedKey], $this->ttls[$sanitizedKey]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = $this->ttls = [];
        return true;
    }
}
