<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Serializers;

class Serializer implements SerializerInterface
{
    public function serialize(mixed $value): string
    {
        return serialize($value);
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function unserialize(string $serializedValue): mixed
    {
        $unserializedValue = false;
        try {
            $unserializedValue = @unserialize($serializedValue);
        } catch (\Throwable) {
        }
        if (false === $unserializedValue) {
            throw new SerializerException('Failed to unserialize value');
        }
        return $unserializedValue;
    }
}
