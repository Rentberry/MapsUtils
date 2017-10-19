<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

/**
 * Cache object
 */
class CacheObject implements CacheInterface
{
    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $cacheKey
     *
     * @return CacheObject
     */
    public function setCacheKey(string $cacheKey): CacheObject
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return CacheObject
     */
    public function setData($data): CacheObject
    {
        $this->data = $data;

        return $this;
    }
}
