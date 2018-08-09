<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

/**
 * Interface Cacheable
 */
interface CacheableInterface
{
    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return string
     */
    public function getCacheKey(): string;
}
