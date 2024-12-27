<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Utils;

use DateInterval;

class CacheValueSerializer
{
    public function serialize(mixed $value, null|int|DateInterval $ttl = null): string
    {
        $ttlSeconds = ($ttl instanceof DateInterval) ? $ttl->s : $ttl;
        return serialize([$value, time(), $ttlSeconds]);
    }

    public function unserialize(string $serializedValue): mixed
    {
        $unserializedArray = unserialize($serializedValue);
        //infinite ttl (null or 0)
        if (!$unserializedArray[2]) {
            return $unserializedArray[0];
        }
        //value expired
        if ((int) $unserializedArray[1] + (int) $unserializedArray[2] <= time()) {
            return null;
        }
        return $unserializedArray[0];
    }
}
