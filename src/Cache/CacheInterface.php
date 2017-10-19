<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Cache;

/**
 * Interface Cache
 */
interface CacheInterface
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
