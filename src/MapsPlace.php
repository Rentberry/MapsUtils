<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Rentberry\MapsUtils\Cache\Cache;
use Rentberry\MapsUtils\Cache\CacheableInterface;
use Rentberry\MapsUtils\Objects\Place;

/**
 * MapsPlace
 */
class MapsPlace implements CacheableInterface
{
    public const QUERY_TYPE_ADDRESS = 'address';
    public const QUERY_TYPE_PLACE_ID = 'place_id';

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
    private $queryType;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var PlaceSimpleFactory
     */
    private $factory;

    /**
     * MapsPlace constructor.
     *
     * @param LoggerInterface    $logger
     * @param Cache              $cache
     * @param string             $apiKey
     * @param PlaceSimpleFactory $factory
     */
    public function __construct(LoggerInterface $logger, Cache $cache, string $apiKey, PlaceSimpleFactory $factory)
    {
        $this->cache = $cache;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->factory = $factory;
    }

    /**
     * @param string $placeId
     *
     * @return Place|null
     */
    public function getPlaceByPlaceId(string $placeId): ?Place
    {
        $obj = clone $this;
        $obj->queryType = self::QUERY_TYPE_PLACE_ID;
        $obj->query = $placeId;

        return $this->getPlaceByObject($obj, true);
    }

    /**
     * @param string $address
     *
     * @param bool   $checkByPoint
     * @return null|Place
     */
    public function getPlaceByAddress(string $address, bool $checkByPoint = true): ?Place
    {
        $obj = clone $this;
        $obj->queryType = self::QUERY_TYPE_ADDRESS;
        $obj->query = $address;

        return $this->getPlaceByObject($obj, $checkByPoint);
    }

    /**
     * @param float $lat
     * @param float $lng
     *
     * @return Place|null
     */
    public function getPlaceByPoint(float $lat, float $lng): ?Place
    {
        $address = \sprintf('%f,%f', $lat, $lng);

        return $this->getPlaceByAddress($address, false);
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return \sprintf('google_raw_data_%s_%s', $this->queryType, \md5($this->query));
    }

    /**
     * Return google raw place data
     *
     * @return mixed[]|null
     */
    public function getData(): ?array
    {
        try {
            $client = $this->getClient();
            $result = $client->get($this->getApiUrl(), ['timeout' => 10]);
            $result = \json_decode($result->getBody()->getContents(), true);

            // Going to save data only if it's correct
            if ($this->isGetPlaceResultCorrect($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Problem with getting google place data. Error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        return ($this->client instanceof Client) ? $this->client : new Client();
    }

    /**
     * @return string
     */
    private function getApiUrl(): string
    {
        return \sprintf(
            'https://maps.googleapis.com/maps/api/geocode/json?%s=%s&key=%s',
            $this->queryType,
            $this->query,
            $this->apiKey
        );
    }

    /**
     * @param mixed[] $result
     *
     * @return bool
     */
    private function isGetPlaceResultCorrect(array $result): bool
    {
        return \array_key_exists('results', $result) && \array_key_exists(0, $result['results']);
    }

    /**
     * @param MapsPlace $obj
     *
     * @return mixed[]|null
     */
    private function getValidatedCacheData(MapsPlace $obj): ?array
    {
        //Get data from the cache, that can be not valid
        $googleData = $this->cache->getData($obj);

        if (!\is_array($googleData) || !$this->isGetPlaceResultCorrect($googleData)) {
            $googleData = $this->cache->updateData($obj);
            if (!\is_array($googleData) || !$this->isGetPlaceResultCorrect($googleData)) {
                throw new \Exception(\sprintf('Place with query %s was not found', $obj->query));
            }
        }

        return $googleData;
    }

    /**
     * @param MapsPlace $obj
     * @param bool      $checkByPoint
     * @return null|Place
     */
    private function getPlaceByObject(MapsPlace $obj, bool $checkByPoint): ?Place
    {
        try {
            $googleData = $this->getValidatedCacheData($obj);

            $place = $this->factory->createPlace($googleData['results']);
            if ($place === null) {
                return null;
            }

            $isComponentsUnderCityNotEmpty =
                $place->getSublocality() !== null
                || $place->getNeighborhoods() !== null
                || $place->getStreet() !== null
                || $place->getStreetNumber() !== null;

            if ($isComponentsUnderCityNotEmpty && $place->getCity() === null && $checkByPoint) {
                $placeByPoint = $this->getPlaceByPoint($place->getLocationLat(), $place->getLocationLng());
                $place->setCity($placeByPoint->getCity());
            }

            return $place;
        } catch (\Throwable $e) {
            $warningMessage = \sprintf(
                'Problem with getting google place by %s. Error: %s',
                $obj->queryType,
                $e->getMessage()
            );
            $this->logger->warning($warningMessage);
        }

        return null;
    }
}
