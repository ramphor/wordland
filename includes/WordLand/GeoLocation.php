<?php
namespace WordLand;

use JsonSerializable;

class GeoLocation implements JsonSerializable
{
    protected $lat;
    protected $long;

    public function __construct($lat, $long)
    {
        $this->setLat($lat);
        $this->setLong($long);
    }

    public function setLat($lat)
    {
        $this->lat = floatval($lat);
    }

    public function setLong($long)
    {
        $this->long = floatval($long);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function toArray()
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize()
    {
        return array(
            'latitude' => $this->lat,
            'longitude' => $this->long
        );
    }
}
