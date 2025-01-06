<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Serializers;

/**
 * Gzinflate Safe serializer adds compression to the standard Safe serializer
 */
class GzdeflateSafeSerializer implements SerializerInterface
{
    private const COMPRESSION_LEVEL = 9;

    public function serialize(mixed $value): string
    {
        $compressed = gzdeflate((new SafeSerializer())->serialize($value), self::COMPRESSION_LEVEL);
        if (false === $compressed) {
            throw new SerializerException('Unable to serialize value');
        }
        return $compressed;
    }

    public function unserialize(string $serializedValue): mixed
    {
        $decompressed = gzinflate($serializedValue);
        if (false === $decompressed) {
            throw new SerializerException('Unable to unserialize value');
        }
        return (new SafeSerializer())->unserialize($decompressed);
    }
}
