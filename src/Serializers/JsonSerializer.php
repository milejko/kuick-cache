<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick
 * @copyright Copyright (c) 2010-2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Serializers;

use JsonException;

/**
 * JSON serializer provides serialization and unserialization functionality, with limitations:
 * unserialized PHP class instances will not be functional (data only)
 */
class JsonSerializer implements SerializerInterface
{
    public function serialize(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new SerializerException('Unable to serialize value');
        }
    }

    public function unserialize(string $serializedValue): mixed
    {
        try {
            return json_decode($serializedValue, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new SerializerException('Unable to unserialize value');
        }
    }
}
