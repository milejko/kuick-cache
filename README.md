# Kuick Cache
[![Latest Version](https://img.shields.io/github/release/milejko/kuick-cache.svg?cacheSeconds=3600)](https://github.com/milejko/kuick-cache/releases)
[![PHP](https://img.shields.io/badge/PHP-8.2%20|%208.3%20|%208.4-blue?logo=php&cacheSeconds=3600)](https://www.php.net)
[![Total Downloads](https://img.shields.io/packagist/dt/kuick/cache.svg?cacheSeconds=3600)](https://packagist.org/packages/kuick/cache)
[![GitHub Actions CI](https://github.com/milejko/kuick-cache/actions/workflows/ci.yml/badge.svg)](https://github.com/milejko/kuick-cache/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/milejko/kuick-cache/graph/badge.svg?token=80QEBDHGPH)](https://codecov.io/gh/milejko/kuick-cache)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?cacheSeconds=14400)](LICENSE)

## PSR-16 Simple Cache implementation
Supporting popular backends such as:
- File system
- Redis
- Database (Doctrine Dbal)
- APCu
- InMemory (aka ArrayCache)
- Layered

## Usage
1. Direct object creation:
```
<?php

use Kuick\Cache\FilesystemCache;

$fileCache = new FilesystemCache('/tmp/cache');
$fileCache->set('foo', 'bar');
echo $fileCache->get('foo'); // bar
```
2. Cache factory:
Factory provides automatic cache object creation by a valid DSN
```
<?php

use Kuick\Cache\CacheFactory;

$cacheFactory = new CacheFactory();

$dbCache    = $cacheFactory('pdo-mysql://127.0.0.1:3306/mydb'); // DbalCache instance
$apcuCache  = $cacheFactory('apcu://');                         // ApcuCache instance
$fileCache  = $cacheFactory('file:///tmp/cache');               // FilesystemCache instance
$redisCache = $cacheFactory('redis://redis-server.com:6379/2'); // RedisCache instance
```
3. Customizing the serializer:<br>
With larger datasets it can be beneficial to use Gzdeflate based serializer.<br>
On the other hand Json based serializers are safer to use, as stored objects are casted to simple, JSON objects.
```
<?php

use Kuick\Cache\CacheFactory;
use Kuick\Cache\FilesystemCache;
use Kuick\Cache\Serializers\GzdeflateJsonSerializer;

$fileCache  = (new CacheFactory())('file:///tmp/cache?serializer=gzdeflate-json');

// equivalent to:

$fileCache  = new FilesystemCache('/tmp/cache', new GzdeflateJsonSerializer());
```

4. Method overview<br>
Kuick Cache implements PSR-16 interface with no exceptions
```
<?php

use Kuick\Cache\InMemoryCache;

$cache = new InMemoryCache();
$cache->set('foo', 'bar', 300);     // set "foo" to "bar", with 5 minutes ttl
$cache->get('foo');                 // "bar"
$cache->get('inexistent, 'default') // "default" (using the default value as the key does not exist)
$cache->has('foo');                 // true
$cache->delete('foo');              // remove "foo"

$cache->setMultiple(['foo' => 'bar', 'bar' => 'baz']); // set "foo" to "bar", and "bar" to "baz"
$cache->getMultiple(['foo', 'bar']);                   // ['foo' => 'bar', 'bar' => 'baz']
$cache->deleteMultiple(['foo', 'bar']);                // removes "foo" and "bar"

$cache->clear(); // removes all the keys
```