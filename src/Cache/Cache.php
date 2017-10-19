<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

/**
 * Cache service
 */
class Cache
{
    /**
     * @var \Memcached
     */
    protected $cache;

    /**
     * @param \Memcached $cache
     */
    public function __construct(\Memcached $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param CacheInterface $object
     *
     * @return false|mixed
     */
    public function getData(CacheInterface $object)
    {
        $cacheKey = $object->getCacheKey();
        $data = $this->cache->get($cacheKey);

        if ($data !== false) {
            return $data;
        }

        $data = $object->getData();

        if ($data) {
            $this->cache->set($cacheKey, $data);
        }

        return $data;
    }

    /**
     * Force update data in cache
     *
     * @param CacheInterface $object
     *
     * @return false|mixed
     */
    public function updateData(CacheInterface $object)
    {
        $cacheKey = $object->getCacheKey();
        $data = $object->getData();

        if ($data) {
            $this->cache->set($cacheKey, $data);
        }

        return $data;
    }
}
