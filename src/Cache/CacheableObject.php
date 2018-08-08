<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

/**
 * Cache object
 */
class CacheableObject implements CacheableInterface
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
     * @return CacheableObject
     */
    public function setCacheKey(string $cacheKey): CacheableObject
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return CacheableObject
     */
    public function setData($data): CacheableObject
    {
        $this->data = $data;

        return $this;
    }
}
