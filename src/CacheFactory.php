<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick.git
 * @copyright Copyright (c) 2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser as DoctrineDsnParser;
use Kuick\Cache\Serializers\GzdeflateJsonSerializer;
use Kuick\Cache\Serializers\GzdeflateSafeSerializer;
use Kuick\Cache\Serializers\JsonSerializer;
use Kuick\Cache\Serializers\SafeSerializer;
use Kuick\Redis\RedisClientFactory;
use Nyholm\Dsn\DsnParser;
use Psr\SimpleCache\CacheInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CacheFactory
{
    /**
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __invoke(string $dsnString): CacheInterface
    {
        $dsn = DsnParser::parse($dsnString);
        $serializer = match ($dsn->getParameter('serializer', 'safe')) {
            'gzdeflate' => new GzdeflateSafeSerializer(),
            'gzdeflate-json' => new GzdeflateJsonSerializer(),
            'json' => new JsonSerializer(),
            'safe' => new SafeSerializer(),
            default => throw new InvalidArgumentException('Serializer invalid: should be one of safe, json, gzdeflate or gzdeflate-json'),
        };
        switch ($dsn->getScheme()) {
            case 'array':
                return new InMemoryCache();
            case 'apcu':
                return new ApcuCache($serializer);
            case 'pdo-sqlite':
            case 'pdo-mysql':
            case 'pdo-pgsql':
                $dsnParser = new DoctrineDsnParser();
                return new DbalCache(DriverManager::getConnection($dsnParser->parse($dsnString)), $serializer);
            case 'file':
                null === $dsn->getPath() &&
                throw new InvalidArgumentException('File cache path not set');
                return new FilesystemCache($dsn->getPath(), $serializer);
            case 'redis':
                $redisClient = (new RedisClientFactory())($dsnString);
                return new RedisCache($redisClient, $serializer);
        }
        throw new InvalidArgumentException('Cache backend invalid: should be one of array, apcu, file or redis');
    }
}
