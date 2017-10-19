<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Google;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * MapsCity
 */
class MapsCity
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param string          $apiKey
     */
    public function __construct(LoggerInterface $logger, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->client = new Client();
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return null|string
     */
    public function getCityByCoordinates(float $latitude, float $longitude): ?string
    {
        try {
            $result = $this->client->get('https://maps.googleapis.com/maps/api/geocode/json?language=en&result_type=political&latlng='.$latitude.','.$longitude.'&key='.$this->apiKey, ['timeout' => 10]);
            $result = \json_decode($result->getBody()->getContents(), true);

            if ($this->isResultCorrect($result)) {
                foreach ($result['results'][0]['address_components'] as $addressComponent) {
                    if (\in_array('locality', $addressComponent['types'], true)) {
                        return $addressComponent['long_name'];
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Can\'t get city by coordinates '.$e->getMessage());
        }

        return null;
    }

    /**
     * @param mixed[] $result
     *
     * @return bool
     */
    private function isResultCorrect(array $result): bool
    {
        return $result['status'] === 'OK' && isset($result['results'][0]);
    }
}
