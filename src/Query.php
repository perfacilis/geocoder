<?php

namespace Perfacilis\Geocoder;

/**
 * @author Roy Arisse <support@perfacilis.com>
 * @copyright (c) 2021, Perfacilis
 */
class Query
{
    public static function fromAddress(string $address): self
    {
        return new self([
            'address' => $address
        ]);
    }

    public static function fromLatLng(float $lat, float $lng): self
    {
        return new self([
            'latlng' => $lat . ',' . $lng
        ]);
    }

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function getParamms(): array
    {
        return $this->params;
    }

    private $params = [];
}
