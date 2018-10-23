<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Cache service
 */
class Cache
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param CacheableInterface $object
     *
     * @return false|mixed
     */
    public function getData(CacheableInterface $object)
    {
        $cacheKey = $object->getCacheKey();
        $data = $this->cache->get($cacheKey, null);

        if ($data !== null) {
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
     * @param CacheableInterface $object
     *
     * @return false|mixed
     */
    public function updateData(CacheableInterface $object)
    {
        $cacheKey = $object->getCacheKey();
        $data = $object->getData();

        if ($data) {
            $this->cache->set($cacheKey, $data);
        }

        return $data;
    }
}
