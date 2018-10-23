<?php declare(strict_types = 1);

namespace Rentberry\MapsUtils\Objects;

/**
 * Place object
 */
class Place
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var float
     */
    protected $locationLat;

    /**
     * @var float
     */
    protected $locationLng;

    /**
     * @var string
     */
    protected $locationType;

    /**
     * @var float
     */
    protected $boundsNortheastLat;

    /**
     * @var float
     */
    protected $boundsNortheastLng;

    /**
     * @var float
     */
    protected $boundsSouthwestLat;

    /**
     * @var float
     */
    protected $boundsSouthwestLng;

    /**
     * @var float
     */
    protected $viewportNortheastLat;

    /**
     * @var float
     */
    protected $viewportNortheastLng;

    /**
     * @var float
     */
    protected $viewportSouthwestLat;

    /**
     * @var float
     */
    protected $viewportSouthwestLng;

    /**
     * @var string
     */
    protected $formattedAddress;

    /**
     * @var mixed[]
     */
    protected $types;

    /**
     * @var mixed[]
     */
    protected $addressComponents;

    /**
     * @var string
     */
    protected $streetNumber;

    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $zip;

    /**
     * @var mixed[]
     */
    protected $source;

    /**
     * @var string
     */
    protected $shortAddress;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $borough;

    /**
     * @var string[]|null
     */
    protected $neighborhoods;

    /**
     * @var string|null
     */
    protected $mainNeighborhood;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Place
     */
    public function setId(string $id): Place
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return float
     */
    public function getLocationLat(): float
    {
        return $this->locationLat;
    }

    /**
     * @param float $locationLat
     *
     * @return Place
     */
    public function setLocationLat(float $locationLat): Place
    {
        $this->locationLat = $locationLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getLocationLng(): float
    {
        return $this->locationLng;
    }

    /**
     * @param float $locationLng
     *
     * @return Place
     */
    public function setLocationLng(float $locationLng): Place
    {
        $this->locationLng = $locationLng;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationType(): string
    {
        return $this->locationType;
    }

    /**
     * @param string $locationType
     *
     * @return Place
     */
    public function setLocationType(string $locationType): Place
    {
        $this->locationType = $locationType;

        return $this;
    }

    /**
     * @return float
     */
    public function getBoundsNortheastLat(): float
    {
        return $this->boundsNortheastLat;
    }

    /**
     * @param float $boundsNortheastLat
     *
     * @return Place
     */
    public function setBoundsNortheastLat(float $boundsNortheastLat): Place
    {
        $this->boundsNortheastLat = $boundsNortheastLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getBoundsNortheastLng(): float
    {
        return $this->boundsNortheastLng;
    }

    /**
     * @param float $boundsNortheastLng
     *
     * @return Place
     */
    public function setBoundsNortheastLng(float $boundsNortheastLng): Place
    {
        $this->boundsNortheastLng = $boundsNortheastLng;

        return $this;
    }

    /**
     * @return float
     */
    public function getBoundsSouthwestLat(): float
    {
        return $this->boundsSouthwestLat;
    }

    /**
     * @param float $boundsSouthwestLat
     *
     * @return Place
     */
    public function setBoundsSouthwestLat(float $boundsSouthwestLat): Place
    {
        $this->boundsSouthwestLat = $boundsSouthwestLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getBoundsSouthwestLng(): float
    {
        return $this->boundsSouthwestLng;
    }

    /**
     * @param float $boundsSouthwestLng
     *
     * @return Place
     */
    public function setBoundsSouthwestLng(float $boundsSouthwestLng): Place
    {
        $this->boundsSouthwestLng = $boundsSouthwestLng;

        return $this;
    }

    /**
     * @return float
     */
    public function getViewportNortheastLat(): float
    {
        return $this->viewportNortheastLat;
    }

    /**
     * @param float $viewportNortheastLat
     *
     * @return Place
     */
    public function setViewportNortheastLat(float $viewportNortheastLat): Place
    {
        $this->viewportNortheastLat = $viewportNortheastLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getViewportNortheastLng(): float
    {
        return $this->viewportNortheastLng;
    }

    /**
     * @param float $viewportNortheastLng
     *
     * @return Place
     */
    public function setViewportNortheastLng(float $viewportNortheastLng): Place
    {
        $this->viewportNortheastLng = $viewportNortheastLng;

        return $this;
    }

    /**
     * @return float
     */
    public function getViewportSouthwestLat(): float
    {
        return $this->viewportSouthwestLat;
    }

    /**
     * @param float $viewportSouthwestLat
     *
     * @return Place
     */
    public function setViewportSouthwestLat(float $viewportSouthwestLat): Place
    {
        $this->viewportSouthwestLat = $viewportSouthwestLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getViewportSouthwestLng(): float
    {
        return $this->viewportSouthwestLng;
    }

    /**
     * @param float $viewportSouthwestLng
     *
     * @return Place
     */
    public function setViewportSouthwestLng(float $viewportSouthwestLng): Place
    {
        $this->viewportSouthwestLng = $viewportSouthwestLng;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param mixed[] $types
     *
     * @return Place
     */
    public function setTypes(array $types): Place
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @param string|null $formattedAddress
     *
     * @return Place
     */
    public function setFormattedAddress(?string $formattedAddress): Place
    {
        $this->formattedAddress = $formattedAddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormattedAddress(): ?string
    {
        return $this->formattedAddress;
    }

    /**
     * @param mixed[] $addressComponents
     *
     * @return Place
     */
    public function setAddressComponents(array $addressComponents): Place
    {
        $this->addressComponents = $addressComponents;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getAddressComponents(): ?array
    {
        return $this->addressComponents;
    }

    /**
     * @param string|null $street
     *
     * @return Place
     */
    public function setStreet(?string $street): Place
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $city
     *
     * @return Place
     */
    public function setCity(?string $city): Place
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $state
     *
     * @return Place
     */
    public function setState(?string $state): Place
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $zip
     *
     * @return Place
     */
    public function setZip(?string $zip): Place
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string|null $streetNumber
     *
     * @return Place
     */
    public function setStreetNumber(?string $streetNumber): Place
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * @param mixed[] $data
     *
     * @return Place
     */
    public function setSource(array $data): Place
    {
        $this->source = $data;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param string $shortAddress
     *
     * @return Place
     */
    public function setShortAddress(string $shortAddress): Place
    {
        $this->shortAddress = $shortAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortAddress(): string
    {
        return $this->shortAddress;
    }

    /**
     * @param string|null $url
     *
     * @return Place
     */
    public function setUrl(?string $url): Place
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getBorough(): ?string
    {
        return $this->borough;
    }

    /**
     * @param string|null $borough
     *
     * @return Place
     */
    public function setBorough(?string $borough): Place
    {
        $this->borough = $borough;

        return $this;
    }

    /**
     * Alias for borough
     *
     * @return string|null
     */
    public function getSublocality(): ?string
    {
        return $this->borough;
    }

    /**
     * Alias for borough
     *
     * @param string|null $borough
     *
     * @return Place
     */
    public function setSublocality(?string $borough): Place
    {
        $this->borough = $borough;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getNeighborhoods(): ?array
    {
        return $this->neighborhoods;
    }

    /**
     * @param string[]|null $neighborhoods
     *
     * @return Place
     */
    public function setNeighborhoods(?array $neighborhoods): Place
    {
        $this->neighborhoods = $neighborhoods;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMainNeighborhood(): ?string
    {
        return $this->mainNeighborhood;
    }

    /**
     * @param string|null $mainNeighborhood
     *
     * @return Place
     */
    public function setMainNeighborhood(?string $mainNeighborhood): Place
    {
        $this->mainNeighborhood = $mainNeighborhood;

        return $this;
    }
}
