<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils;

use Psr\Log\LoggerInterface;
use Rentberry\MapsUtils\Objects\Place;

/**
 * PlaceSimpleFactory
 */
class PlaceSimpleFactory
{
    /**
     * @const float
     */
    protected const OVERSIGHT = 0.005;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PlaceSimpleFactory constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Fields can be not set if not valid googleDataResults provided.
     *
     * @param mixed[] $googleDataResults
     * @return null|Place
     */
    public function createPlace(array $googleDataResults): ?Place
    {
        try {
            $place = new Place();
            $place->setSource($googleDataResults);

            $place = $this->setBaseFields($googleDataResults, $place);
            $place = $this->setAddressFields($googleDataResults, $place);
            $place = $this->setShortAddress($place);
            $place = $this->setMapUrl($place);

            return $place;
        } catch (\Throwable $e) {
            $this->logger->warning('Can\'t create place object from results array: '.$e->getMessage());

            return null;
        }
    }


    /**
     * Set address fields such as streetNumber, street, city, state, locality, sublocality and neighborhoods
     * via calling private methods of this factory for set these fields by component type.
     * All fields are collected first into one composite array.
     * Separately defined handling of field mainNeighborhood - if it exists in first result provided with
     * googleData, then it will be set as main. Elsewhere, first neighborhood of collection will be set as main.
     *
     * @param mixed[] $googleDataResults
     * @param Place   $place
     * @return Place
     */
    protected function setAddressFields(array $googleDataResults, Place $place): Place
    {
        $compositeComponents = [];

        foreach ($googleDataResults as $googleDataResult) {
            foreach ($googleDataResult['address_components'] as $component) {
                if (!\in_array($component, $compositeComponents)) {
                    \array_push($compositeComponents, $component);
                }
            }
        }

        foreach ($compositeComponents as $component) {
            foreach ($component['types'] as $type) {
                $setter = 'set'.$this->camelize($type);
                if (\method_exists($this, $setter)) {
                    $place = $this->$setter($component, $place);
                }
            }
        }

        $place = $this->setMainNeighborhood($googleDataResults, $place);

        return $place;
    }

    /**
     * @param mixed[] $googleDataResults
     * @param Place   $place
     * @return Place
     */
    protected function setMainNeighborhood(array $googleDataResults, Place $place): Place
    {
        $firstResult = $googleDataResults[0];
        foreach ($firstResult['address_components'] as $component) {
            foreach ($component['types'] as $type) {
                if ($type === 'neighborhood' && !$place->getMainNeighborhood()) {
                    $place->setMainNeighborhood($component['long_name']);
                }
            }
        }
        if (!$place->getMainNeighborhood() && $place->getNeighborhoods() !== null) {
            $place->setMainNeighborhood($place->getNeighborhoods()[0]);
        }

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setStreetNumber(array $component, Place $place): Place
    {
        $place->setStreetNumber($component['long_name']);

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setRoute(array $component, Place $place): Place
    {
        $place->setStreet($component['short_name']);

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setLocality(array $component, Place $place): Place
    {
        $place->setCity($component['long_name']);

        return $place;
    }

    /**
     * Postal town as alternative data if locality isn't present
     *
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setPostalTown(array $component, Place $place): Place
    {
        $place->setCity($component['long_name']);

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setAdministrativeAreaLevel1(array $component, Place $place): Place
    {
        $place->setState($component['short_name']);

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setPostalCode(array $component, Place $place): Place
    {
        $place->setZip($component['long_name']);

        return $place;
    }

    /**
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setSublocality(array $component, Place $place): Place
    {
        $place->setSublocality($component['long_name']);

        return $place;
    }

    /**
     * Creating/appending neighborhoods collection from component of result.
     *
     * @param mixed[] $component
     * @param Place   $place
     * @return Place
     */
    protected function setNeighborhood(array $component, Place $place): Place
    {
        $neighborhoods = $place->getNeighborhoods() ?? [];

        if (!\in_array($component['long_name'], $neighborhoods)) {
            $neighborhoods[] = $component['long_name'];
        }
        $place->setNeighborhoods($neighborhoods);

        return $place;
    }

    /**
     * Basic data such as geometry or types, etc. - provided only
     * with first result (basic for this place). So from $googleDataResults
     * retrieved first element (always!)
     *
     *
     * @param mixed[] $googleDataResults
     * @param Place   $place
     * @return Place
     */
    protected function setBaseFields(array $googleDataResults, Place $place): Place
    {
        $firstResult = $googleDataResults[0];

        $place->setId($firstResult['place_id'])
            ->setTypes($firstResult['types'])
            ->setFormattedAddress($firstResult['formatted_address'])
            ->setLocationLat($firstResult['geometry']['location']['lat'])
            ->setLocationLng($firstResult['geometry']['location']['lng'])
            ->setAddressComponents($firstResult['address_components']);

        if (isset($firstResult['geometry']['location_type'])) {
            $place->setLocationType($firstResult['geometry']['location_type']);
        }

        $place = $this->setBounds($firstResult, $place);
        $place = $this->setViewport($firstResult, $place);

        return $place;
    }

    /**
     * @param mixed[] $result
     * @param Place   $place
     *
     * @return Place
     */
    protected function setBounds(array $result, Place $place): Place
    {
        if ($this->isBoundsCorrect($result)) {
            return $place
                ->setBoundsNortheastLat((float) $result['geometry']['bounds']['northeast']['lat'])
                ->setBoundsNortheastLng((float) $result['geometry']['bounds']['northeast']['lng'])
                ->setBoundsSouthwestLat((float) $result['geometry']['bounds']['southwest']['lat'])
                ->setBoundsSouthwestLng((float) $result['geometry']['bounds']['southwest']['lng']);
        }

        if ($this->isViewportCorrect($result)) {
            return $place
                ->setBoundsNortheastLat((float) $result['geometry']['viewport']['northeast']['lat'])
                ->setBoundsNortheastLng((float) $result['geometry']['viewport']['northeast']['lng'])
                ->setBoundsSouthwestLat((float) $result['geometry']['viewport']['southwest']['lat'])
                ->setBoundsSouthwestLng((float) $result['geometry']['viewport']['southwest']['lng']);
        }

        return $place
            ->setBoundsNortheastLat((float) $result['geometry']['location']['lat'] + self::OVERSIGHT)
            ->setBoundsNortheastLng((float) $result['geometry']['location']['lng'] + self::OVERSIGHT)
            ->setBoundsSouthwestLat((float) $result['geometry']['location']['lat'] - self::OVERSIGHT)
            ->setBoundsSouthwestLng((float) $result['geometry']['location']['lng'] - self::OVERSIGHT);
    }

    /**
     * @param mixed[] $result
     * @param Place   $place
     *
     * @return Place
     */
    protected function setViewport(array $result, Place $place): Place
    {
        if ($this->isViewportCorrect($result)) {
            return $place
                ->setViewportNortheastLat((float) $result['geometry']['viewport']['northeast']['lat'])
                ->setViewportNortheastLng((float) $result['geometry']['viewport']['northeast']['lng'])
                ->setViewportSouthwestLat((float) $result['geometry']['viewport']['southwest']['lat'])
                ->setViewportSouthwestLng((float) $result['geometry']['viewport']['southwest']['lng']);
        }

        if ($this->isBoundsCorrect($result)) {
            return $place
                ->setViewportNortheastLat((float) $result['geometry']['bounds']['northeast']['lat'])
                ->setViewportNortheastLng((float) $result['geometry']['bounds']['northeast']['lng'])
                ->setViewportSouthwestLat((float) $result['geometry']['bounds']['southwest']['lat'])
                ->setViewportSouthwestLng((float) $result['geometry']['bounds']['southwest']['lng']);
        }

        return $place
            ->setViewportNortheastLat((float) $result['geometry']['location']['lat'] + self::OVERSIGHT)
            ->setViewportNortheastLng((float) $result['geometry']['location']['lng'] + self::OVERSIGHT)
            ->setViewportSouthwestLat((float) $result['geometry']['location']['lat'] - self::OVERSIGHT)
            ->setViewportSouthwestLng((float) $result['geometry']['location']['lng'] - self::OVERSIGHT);
    }


    /**
     * @param Place $place
     * @return Place
     */
    protected function setShortAddress(Place $place): Place
    {
        $shortAddress = \sprintf(
            '%s %s%s%s%s%s',
            $place->getStreetNumber(),
            $place->getStreet(),
            $place->getCity() ? ', ' : '',
            $place->getCity(),
            $place->getState() ? ', ' : '',
            $place->getState()
        );

        $place->setShortAddress($shortAddress);

        return $place;
    }

    /**
     * @param Place $place
     * @return Place
     */
    protected function setMapUrl(Place $place): Place
    {
        $url = $this->formatUrl($place);
        $place->setUrl($url);

        return $place;
    }

    /**
     * @param Place $place
     *
     * @return string
     */
    protected function formatUrl(Place $place): string
    {
        return \sprintf(
            'https://www.google.com/maps/place/%s/@%s,%s,17z',
            $place->getFormattedAddress(),
            $place->getLocationLat(),
            $place->getLocationLng()
        );
    }

    /**
     * @param mixed[] $googlePlace
     *
     * @return bool
     */
    protected function isViewportCorrect(array $googlePlace): bool
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
    protected function isBoundsCorrect(array $googlePlace): bool
    {
        return \array_key_exists('bounds', $googlePlace['geometry'])
            && isset($googlePlace['geometry']['bounds']['northeast']['lat'])
            && isset($googlePlace['geometry']['bounds']['northeast']['lng'])
            && isset($googlePlace['geometry']['bounds']['southwest']['lat'])
            && isset($googlePlace['geometry']['bounds']['southwest']['lng'])
            ;
    }

    /**
     * @param string $input
     * @return string The camelized string
     */
    private function camelize(string $input): string
    {
        return \strtr(\ucwords(\strtr($input, array('_' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => ''));
    }
}
