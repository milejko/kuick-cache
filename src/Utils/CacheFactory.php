<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick)
 *
 * @link      https://github.com/milejko/kuick.git
 * @copyright Copyright (c) 2024 Mariusz Miłejko (mariusz@milejko.pl)
 * @license   https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Kuick\Cache\Utils;

use Kuick\Cache\ApcuCache;
use Kuick\Cache\ArrayCache;
use Kuick\Cache\FileCache;
use Kuick\Cache\InvalidArgumentException;
use Kuick\Cache\RedisCache;
use Kuick\Redis\RedisClientFactory;
use Nyholm\Dsn\DsnParser;
use Psr\SimpleCache\CacheInterface;

class CacheFactory
{
    /**
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __invoke(string $dsnString): CacheInterface
    {
        $dsn = DsnParser::parse($dsnString);
        switch ($dsn->getScheme()) {
            case 'array':
                return new ArrayCache();
            case 'apcu':
                return new ApcuCache();
            case 'file':
                return new FileCache($dsn->getPath());
            case 'redis':
                $redisClient = (new RedisClientFactory())($dsnString);
                return new RedisCache($redisClient);
        }
        throw new InvalidArgumentException('Cache backend invalid: should be one of array, apcu, file or redis');
    }
}
