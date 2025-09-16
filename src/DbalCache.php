<?php

namespace Kuick\Cache;

use DateInterval;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Kuick\Cache\Serializers\PhpSerializer;
use Kuick\Cache\Serializers\SerializerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class DbalCache extends AbstractCache implements CacheInterface
{
    private const TABLE_NAME = 'cache';
    private const ID_COLUMN = 'id';
    private const DATA_COLUMN = 'data';
    private const TTL_COLUMN = 'ttl';

    public function __construct(
        private Connection $dbal,
        private SerializerInterface $serializer = new PhpSerializer(),
    ) {
        $schema = $dbal->createSchemaManager();
        if ($schema->tablesExist(self::TABLE_NAME)) {
            return;
        }
        $cache = new Table(self::TABLE_NAME);
        $cache->addColumn(self::ID_COLUMN, 'string', ['length' => 32]);
        $cache->addColumn(self::DATA_COLUMN, 'blob');
        $cache->addColumn(self::TTL_COLUMN, 'integer', ['notnull' => false]);
        $cache->setPrimaryKey([self::ID_COLUMN]);
        $schema->createTable($cache);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->validateKey($key);
        $query = $this->dbal->createQueryBuilder()
            ->select(self::DATA_COLUMN)
            ->from(self::TABLE_NAME)
            ->where(self::ID_COLUMN . ' = :id and (' . self::TTL_COLUMN . ' = 0 or ' . self::TTL_COLUMN . ' > :now)')
            ->setParameters([
                self::ID_COLUMN => $this->getId($key),
                'now' => time(),
            ]);
        $result = $query->fetchOne();
        if (!is_string($result)) {
            return $default;
        }
        return $this->serializer->unserialize($result);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->validateKey($key);
        $intTtl = $this->ttlToInt($ttl);
        $params = [
            self::ID_COLUMN => $this->getId($key),
            self::DATA_COLUMN => $this->serializer->serialize($value),
            self::TTL_COLUMN => 0 === $intTtl ? 0 : time() + $intTtl,
        ];
        // try inserting data
        try {
            $this->dbal->insert(self::TABLE_NAME, $params);
        } catch (Throwable) {
            $this->dbal->update(self::TABLE_NAME, $params, [self::ID_COLUMN => $this->getId($key)]);
        }
        return true;
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);
        return null !== $this->get($key);
    }

    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $this->dbal->delete(self::TABLE_NAME, [self::ID_COLUMN => $this->getId($key)]);
        return true;
    }

    public function clear(): bool
    {
        $this->dbal->createQueryBuilder()->delete(self::TABLE_NAME)
            ->executeQuery();
        return true;
    }

    public function getId(string $key): string
    {
        return md5($key);
    }
}
