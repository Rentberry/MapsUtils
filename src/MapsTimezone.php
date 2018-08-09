<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Rentberry\MapsUtils\Cache\Cache;
use Rentberry\MapsUtils\Cache\CacheableInterface;

/**
 * MapsTimezone
 */
class MapsTimezone implements CacheableInterface
{
    private const LOCATION_ROUND_LEVEL = 3;
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

    /**
     * MapsTimezone constructor.
     *
     * @param LoggerInterface $logger
     * @param Cache           $cache
     * @param string          $apiKey
     */
    public function __construct(LoggerInterface $logger, Cache $cache, string $apiKey)
    {
        $this->cache = $cache;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    /**
     * @param float $lat
     * @param float $lng
     *
     * @return null|string
     */
    public function getTimezonePoint(float $lat, float $lng): ?string
    {
        $this->lat = $lat;
        $this->lng = $lng;

        return $googleData = $this->getValidatedCacheData();
    }

    /**
     * Return google raw timezone data
     *
     * @return mixed[]|null
     */
    public function getData(): ?array
    {
        try {
            $roundedLattitude = \round($this->lat, self::LOCATION_ROUND_LEVEL);
            $roundedLongitude = \round($this->lng, self::LOCATION_ROUND_LEVEL);
            $dt = new \DateTime();

            $result = $this->getClient()->get($this->getApiUrl($roundedLattitude, $roundedLongitude, $dt), ['timeout' => 10]);
            $result = \json_decode($result->getBody()->getContents(), true);

            // Going to save data only if it's correct
            if ($this->isTimezoneResultCorrect($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Problem with getting google place data. Error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        $roundedLattitude = \round($this->lat, self::LOCATION_ROUND_LEVEL);
        $roundedLongitude = \round($this->lng, self::LOCATION_ROUND_LEVEL);

        return \sprintf('%s_%s', 'google_timezone_data', sha1($roundedLattitude.$roundedLongitude, false));
    }

    /**
     * @param mixed[] $result`
     *
     * @return bool
     */
    private function isTimezoneResultCorrect(array $result): bool
    {
        return \is_array($result) && \array_key_exists('status', $result) && $result['status'] === 'OK';
    }

    /**
     * @return null|string
     */
    private function getValidatedCacheData(): ?string
    {
        //Get data from the cache, that can be not valid
        $googleData = $this->cache->getData($this);

        if (!\is_array($googleData) || !$this->isTimezoneResultCorrect($googleData)) {
            $googleData = $this->cache->updateData($this);
        }

        return $googleData['timeZoneId'] ?? null;
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        if (!($this->client instanceof Client)) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * @param float     $lat
     * @param float     $lng
     * @param \DateTime $dateTime
     *
     * @return string
     */
    private function getApiUrl(float $lat, float $lng, \DateTime $dateTime): string
    {
        return \sprintf(
            'https://maps.googleapis.com/maps/api/timezone/json?location=%s,%s&timestamp=%s&key=%s',
            $lat,
            $lng,
            $dateTime->getTimestamp(),
            $this->apiKey
        );
    }
}
