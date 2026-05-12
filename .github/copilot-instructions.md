# Kuick Cache – Copilot Instructions

## Project overview
PHP library providing a PSR-16 Simple Cache implementation. Namespace: `Kuick\Cache`. Tests namespace: `Tests\Kuick\Cache`. Requires PHP ≥ 8.2. Tested on PHP 8.2–8.5.

## Commands

```bash
# Run all checks (CS, PHPStan, PHPMD, PHPUnit)
composer test:all

# Individual checks
composer test:phpcs       # PSR-12 code style
composer test:phpstan     # Static analysis (level 9)
composer test:phpmd       # Mess detector
composer test:phpunit     # PHPUnit with coverage

# Auto-fix code style
composer fix:phpcbf

# Run a single test class
XDEBUG_MODE=coverage vendor/bin/phpunit tests/Unit/FilesystemCacheTest.php

# Run a single test method
XDEBUG_MODE=coverage vendor/bin/phpunit --filter testIfCacheCanBeSetAndGet

# Full Docker-based CI run (mirrors CI pipeline)
make test
```

## Architecture

All cache backends extend `AbstractCache`, which provides:
- `getMultiple` / `setMultiple` / `deleteMultiple` (delegating to single-key methods)
- `validateKey` — enforces max 512-char keys; throws `InvalidArgumentException`
- `sanitizeKey` — URL-encodes keys; overridden in `FilesystemCache` to prepend base path
- `ttlToInt` — normalises `null|int|DateInterval` TTL to int (0 = no expiry)

**Backends**: `FilesystemCache`, `RedisCache`, `DbalCache`, `ApcuCache`, `InMemoryCache`, `NullCache`, `LayeredCache`

**Serializers** (in `src/Serializers/`): `PhpSerializer` (default), `JsonSerializer`, `GzipSerializer`, `GzipJsonSerializer` — all implement `SerializerInterface`.

**`CacheFactory`** is an invokable class that parses a DSN string (via `nyholm/dsn`) and returns the appropriate `CacheInterface` instance. Serializer is set via `?serializer=` query param in the DSN. Supported DSN schemes:
- `file:///path` → `FilesystemCache`
- `redis://host:port/db` → `RedisCache`
- `pdo-mysql://`, `pdo-pgsql://`, `pdo-sqlite://` → `DbalCache`
- `apcu://` → `ApcuCache`
- `in-memory://` → `InMemoryCache`
- `null://` → `NullCache`

**`LayeredCache`** takes an ordered array of backends (fastest first). On a cache hit it back-fills all backends that missed. On write/delete it propagates to every layer.

**FilesystemCache storage format**: each cache file contains `{expiration_unix_timestamp}|{serialized_value}`. Expiration `0` means no expiry.

**RedisCache null-TTL handling**: sets with a 10-year TTL (`315360000` seconds), then immediately calls `persist()` to remove the TTL at the Redis level.

## Key conventions

- **Error handling**: backend failures throw `CacheException`; invalid keys/arguments throw `InvalidArgumentException`. Both live in `src/` and correspond to PSR-16 exception interfaces.
- **Test structure**: one test file per cache class under `tests/Unit/`. Each test class uses `#[CoversClass(TargetClass::class)]` and `@SuppressWarnings` PHPDoc where PHPMD triggers false positives.
- **PHPStan suppressions**: use `@SuppressWarnings(...)` PHPDoc (PHPMD) and `/** @SuppressWarnings */` inline comments for known false positives (e.g. `CouplingBetweenObjects` on `CacheFactory`, `ErrorControlOperator` in `FilesystemCache`).
- **Code style**: PSR-12, enforced by `phpcs`/`phpcbf`.
- **Test mocks/fixtures**: placed under `tests/Mocks/`. `tearDownAfterClass` removes the entire `Mocks/` directory to keep the working tree clean.
- **CI**: runs on every push/PR across PHP 8.2–8.5 via Docker (`make test`), uploads coverage to Codecov.
