<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Serializers;

class SafeSerializer implements SerializerInterface
{
    public function serialize(mixed $value): string
    {
        return serialize($value);
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function unserialize(string $serializedValue): mixed
    {
        $unserializedValue = @unserialize($serializedValue);
        if (false === $unserializedValue) {
            throw new SerializerException('Failed to unserialize value');
        }
        return $unserializedValue;
    }
}
