<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils;

use Rentberry\MapsUtils\Objects\Place;

/**
 * Contains logic for search address on mapsPlace
 */
class SearchMapsPlace
{
    /**
     * @var MapsPlace
     */
    private $mapsPlace;

    /**
     * SearchMapsPlace constructor.
     *
     * @param MapsPlace $mapsPlace
     */
    public function __construct(MapsPlace $mapsPlace)
    {
        $this->mapsPlace = $mapsPlace;
    }

    /**
     * @param string      $city
     * @param string      $zip
     * @param null|string $state
     * @param null|string $country
     *
     * @return Place|null
     */
    public function searchZipPlace(
        string $city,
        string $zip,
        ?string $state = null,
        ?string $country = 'USA'
    ): ?Place {
        return $this->mapsPlace->getPlaceByAddress(
            \sprintf('%s %s %s %s', $city, $state, $zip, $country)
        );
    }

    /**
     * @param string      $city
     * @param null|string $state
     * @param null|string $country
     *
     * @return Place|null
     */
    public function searchCityPlace(
        string $city,
        ?string $state = null,
        ?string $country = 'USA'
    ): ?Place {
        return $this->mapsPlace->getPlaceByAddress(
            \sprintf('%s %s %s', $city, $state, $country)
        );
    }

    /**
     * @param string      $neighborhood
     * @param string      $city
     * @param null|string $state
     * @param null|string $sublocality
     * @param null|string $country
     *
     * @return Place|null
     */
    public function searchNeighborhoodPlace(
        string $neighborhood,
        string $city,
        ?string $state = null,
        ?string $sublocality = null,
        ?string $country = 'USA'
    ): ?Place {
        return $this->mapsPlace->getPlaceByAddress(
            \sprintf('%s %s %s %s %s', $neighborhood, $sublocality, $city, $state, $country)
        );
    }

    /**
     * @param string      $sublocality
     * @param string      $city
     * @param null|string $state
     * @param null|string $country
     *
     * @return Place|null
     */
    public function searchSublocalityPlace(
        string $sublocality,
        string $city,
        ?string $state = null,
        ?string $country = 'USA'
    ): ?Place {
        return $this->mapsPlace->getPlaceByAddress(
            \sprintf('%s %s %s %s', $sublocality, $city, $state, $country)
        );
    }
}
