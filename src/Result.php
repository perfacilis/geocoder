<?php

namespace Perfacilis\Geocoder;

use Exception;
use InvalidArgumentException;

/**
 * @author Roy Arisse <support@perfacilis.com>
 * @copyright (c) 2021, Perfacilis
 */
class Result
{
    public function __construct(array $attr)
    {
        $this->attr = $attr;
    }

    /**
     * Alias for getRoute
     */
    public function getStreet(): string
    {
        return $this->getRoute();
    }

    public function getStreetNumber(): string
    {
        return $this->getAddressComponent('street_number');
    }

    public function getRoute()
    {
        return $this->getAddressComponent('route');
    }

    public function getLocality()
    {
        return $this->getAddressComponent('locality');
    }

    public function getCounty()
    {
        return $this->getAddressComponent('administrative_area_level_2');
    }

    public function getState()
    {
        return $this->getAddressComponent('administrative_area_level_1');
    }

    public function getCountry()
    {
        return $this->getAddressComponent('country');
    }

    public function getPostalCode()
    {
        return $this->getAddressComponent('postal_code');
    }

    public function getFormattedAddress()
    {
        return $this->attr['formatted_address'];
    }

    public function getPlaceId()
    {
        return $this->attr['place_id'];
    }

    public function getLat()
    {
        return $this->attr['geometry']['location']['lat'];
    }

    public function getLng()
    {
        return $this->attr['geometry']['location']['lng'];
    }

    private $attr = [];

    private function getAddressComponent(string $key): string
    {
        foreach ($this->attr['address_components'] as $c) {
            if (in_array($key, $c['types'])) {
                return $c['long_name'];
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Given component \'%s\' doesn\'t exist.',
            $key
        ));
    }

    public function __get($name)
    {
        if (isset($this->attr[$name])) {
            return $this->attr[$name];
        }

        throw new Exception(sprintf(
            'Given propertly \'%s\' doesn\'t exist.',
            $name
        ));
    }
}
