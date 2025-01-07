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
Supported serializers:
- Standard PHP serializer
- Gzdeflate supporting data compression
- Json based serializer (fast, slim, and safe)

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
$apcuCache  = $cacheFactory('apcu://'); // ApcuCache instance
$fileCache  = $cacheFactory('file:///tmp/cache'); // FilesystemCache instance
$redisCache = $cacheFactory('redis://redis-server.com:6379/2'); // RedisCache instance
```