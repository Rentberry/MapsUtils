<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Google;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Rentberry\MapsUtils\Cache\Cache;
use Rentberry\MapsUtils\Cache\CacheInterface;
use Rentberry\MapsUtils\Google\Objects\Place;

/**
 * Class MapsPlace
 */
class MapsPlace implements CacheInterface
{
    public const QUERY_TYPE_ADDRESS = 'address';
    public const QUERY_TYPE_PLACE_ID = 'place_id';
    public const NEW_YORK_LONG_NAME = 'New York';
    public const NEW_YORK_BOROUGHS = [
        'Manhattan',
        'Bronx',
        'Brooklyn',
        'Queens',
        'Staten Island',
    ];

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
     * @var float
     */
    private $oversight = 0.005;

    /**
     * @param LoggerInterface $logger
     * @param \Memcached      $memcached
     * @param string          $apiKey
     */
    public function __construct(LoggerInterface $logger, \Memcached $memcached, string $apiKey)
    {
        $this->cache = new Cache($memcached);
        $this->apiKey = $apiKey;
        $this->logger = $logger;
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

        try {
            $googleData = $this->getValidatedCacheData($obj);

            return $this->createPlaceFromResult($googleData);
        } catch (\Throwable $e) {
            $this->logger->warning('Problem with getting google place by id. Error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * @param string $address
     *
     * @return Place|null
     */
    public function getPlaceByAddress(string $address): ?Place
    {
        $obj = clone $this;
        $obj->queryType = self::QUERY_TYPE_ADDRESS;
        $obj->query = $address;

        try {
            $googleData = $this->getValidatedCacheData($obj);

            return $this->createPlaceFromResult($googleData);
        } catch (\Throwable $e) {
            $this->logger->warning('Problem with getting google place by address. Error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return \sprintf('google_raw_data_%s_%s', $this->queryType, \md5($this->query));
    }

    /**
     * Return googlePlace object, created from google data
     *
     * @param mixed[] $data
     *
     * @return Place|null
     */
    public function getPlaceFromGoogleData(array $data): ?Place
    {
        try {
            return $this->createPlaceFromResult($data);
        } catch (\Throwable $e) {
            $this->logger->warning('Problem with getting google place by google data. Error: '.$e->getMessage());
        }

        return null;
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
     * Check if city is borough
     *
     * @param string $name
     *
     * @return bool
     */
    public function isBorough(string $name): bool
    {
        return \in_array(
            \mb_strtolower($name),
            \array_map('strtolower', self::NEW_YORK_BOROUGHS),
            true
        );
    }

    /**
     * @param mixed[] $result
     *
     * @return Place
     */
    public function createPlaceFromResult(array $result): Place
    {
        $googlePlace = $result['results'][0];
        $place = new Place();
        $place->setId($googlePlace['place_id'])
            ->setTypes($googlePlace['types'])
            ->setAddressComponents($googlePlace['address_components'])
            ->setFormattedAddress($googlePlace['formatted_address'])
            ->setLocationLat($googlePlace['geometry']['location']['lat'])
            ->setLocationLng($googlePlace['geometry']['location']['lng']);

        $place = $this->setBounds($googlePlace, $place);
        $place = $this->setViewport($googlePlace, $place);

        foreach ($place->getAddressComponents() as $addressComponent) {
            foreach ((array) $addressComponent['types'] as $type) {
                if ($type === 'street_number') {
                    $place->setStreetNumber($addressComponent['long_name']);
                } elseif ($type === 'route') {
                    $place->setStreet($addressComponent['short_name']);
                } elseif ($type === 'locality') {
                    $place->setCity($addressComponent['long_name']);
                } elseif ($type === 'administrative_area_level_1') {
                    $place->setState($addressComponent['short_name']);
                } elseif ($type === 'postal_code') {
                    $place->setZip($addressComponent['long_name']);
                } elseif ($type === 'sublocality' && !$place->getCity()) {
                    if (!$this->isBorough($addressComponent['long_name'])) {
                        $place->setCity($addressComponent['long_name']);
                    } else {
                        $place->setCity(self::NEW_YORK_LONG_NAME);
                    }
                } elseif ($type === 'sublocality_level_1') {
                    $place->setBorough($addressComponent['long_name']);
                } elseif ($type === 'administrative_area_level_2' && !$place->getCity()) {
                    $place->setCity($addressComponent['short_name']);
                } elseif ($type === 'neighborhood') {
                    $place->setNeighborhood($addressComponent['long_name']);
                }
            }
        }

        $shortAddress = \sprintf(
            '%s %s%s%s%s%s',
            $place->getStreetNumber(),
            $place->getStreet(),
            $place->getCity() ? ', ' : '',
            $place->getCity(),
            $place->getState() ? ', ' : '',
            $place->getState()
        );

        $mapUrl = $this->formatUrl($place);

        $place->setShortAddress($shortAddress);
        $place->setUrl($mapUrl);

        $googlePlace['url'] = $mapUrl;
        $googlePlace['shortAddress'] = $shortAddress;
        $place->setSource($googlePlace);

        return $place;
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

        return $this->getPlaceByAddress($address);
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
     * @param mixed[] $googlePlace
     * @param Place   $place
     *
     * @return Place
     */
    private function setBounds(array $googlePlace, Place $place): Place
    {
        if ($this->isBoundsCorrect($googlePlace)) {
            return $place
                ->setBoundsNortheastLat((float) $googlePlace['geometry']['bounds']['northeast']['lat'])
                ->setBoundsNortheastLng((float) $googlePlace['geometry']['bounds']['northeast']['lng'])
                ->setBoundsSouthwestLat((float) $googlePlace['geometry']['bounds']['southwest']['lat'])
                ->setBoundsSouthwestLng((float) $googlePlace['geometry']['bounds']['southwest']['lng']);
        }

        if ($this->isViewportCorrect($googlePlace)) {
            return $place
                ->setBoundsNortheastLat((float) $googlePlace['geometry']['viewport']['northeast']['lat'])
                ->setBoundsNortheastLng((float) $googlePlace['geometry']['viewport']['northeast']['lng'])
                ->setBoundsSouthwestLat((float) $googlePlace['geometry']['viewport']['southwest']['lat'])
                ->setBoundsSouthwestLng((float) $googlePlace['geometry']['viewport']['southwest']['lng']);
        }

        return $place
            ->setBoundsNortheastLat((float) $googlePlace['geometry']['location']['lat'] + $this->oversight)
            ->setBoundsNortheastLng((float) $googlePlace['geometry']['location']['lng'] + $this->oversight)
            ->setBoundsSouthwestLat((float) $googlePlace['geometry']['location']['lat'] - $this->oversight)
            ->setBoundsSouthwestLng((float) $googlePlace['geometry']['location']['lng'] - $this->oversight);
    }

    /**
     * @param mixed[] $googlePlace
     * @param Place   $place
     *
     * @return Place
     */
    private function setViewport(array $googlePlace, Place $place): Place
    {
        if ($this->isViewportCorrect($googlePlace)) {
            return $place
                ->setViewportNortheastLat((float) $googlePlace['geometry']['viewport']['northeast']['lat'])
                ->setViewportNortheastLng((float) $googlePlace['geometry']['viewport']['northeast']['lng'])
                ->setViewportSouthwestLat((float) $googlePlace['geometry']['viewport']['southwest']['lat'])
                ->setViewportSouthwestLng((float) $googlePlace['geometry']['viewport']['southwest']['lng']);
        }

        if ($this->isBoundsCorrect($googlePlace)) {
            return $place
                ->setViewportNortheastLat((float) $googlePlace['geometry']['bounds']['northeast']['lat'])
                ->setViewportNortheastLng((float) $googlePlace['geometry']['bounds']['northeast']['lng'])
                ->setViewportSouthwestLat((float) $googlePlace['geometry']['bounds']['southwest']['lat'])
                ->setViewportSouthwestLng((float) $googlePlace['geometry']['bounds']['southwest']['lng']);
        }

        return $place
            ->setViewportNortheastLat((float) $googlePlace['geometry']['location']['lat'] + $this->oversight)
            ->setViewportNortheastLng((float) $googlePlace['geometry']['location']['lng'] + $this->oversight)
            ->setViewportSouthwestLat((float) $googlePlace['geometry']['location']['lat'] - $this->oversight)
            ->setViewportSouthwestLng((float) $googlePlace['geometry']['location']['lng'] - $this->oversight);
    }

    /**
     * @param Place $place
     *
     * @return string
     */
    private function formatUrl(Place $place): string
    {
        return \sprintf(
            'https://www.google.com/maps/place/%s/@%s,%s,17z',
            $place->getFormattedAddress(),
            $place->getLocationLat(),
            $place->getLocationLng()
        );
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
                throw new \HttpException(\sprintf('Place with query %s was not found', $obj->query));
            }
        }

        return $googleData;
    }

    /**
     * @param mixed[] $googlePlace
     *
     * @return bool
     */
    private function isViewportCorrect(array $googlePlace): bool
    {
        return \array_key_exists('viewport', $googlePlace['geometry'])
            && isset($googlePlace['geometry']['viewport']['northeast']['lat'])
            && isset($googlePlace['geometry']['viewport']['northeast']['lng'])
            && isset($googlePlace['geometry']['viewport']['southwest']['lat'])
            && isset($googlePlace['geometry']['viewport']['southwest']['lng'])
        ;
    }

    /**
     * @param mixed[] $googlePlace
     *
     * @return bool
     */
    private function isBoundsCorrect(array $googlePlace): bool
    {
        return \array_key_exists('bounds', $googlePlace['geometry'])
            && isset($googlePlace['geometry']['bounds']['northeast']['lat'])
            && isset($googlePlace['geometry']['bounds']['northeast']['lng'])
            && isset($googlePlace['geometry']['bounds']['southwest']['lat'])
            && isset($googlePlace['geometry']['bounds']['southwest']['lng'])
        ;
    }
}
