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
 * GZip serializer adds compression to the standard serializer
 */
class GzipSerializer implements SerializerInterface
{
    private const COMPRESSION_LEVEL = 9;

    public function serialize(mixed $value): string
    {
        return (string) gzdeflate((new Serializer())->serialize($value), self::COMPRESSION_LEVEL);
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function unserialize(string $serializedValue): mixed
    {
        $decompressed = @gzinflate($serializedValue);
        if (false === $decompressed) {
            throw new SerializerException('Unable to unserialize value');
        }
        return (new Serializer())->unserialize($decompressed);
    }
}
