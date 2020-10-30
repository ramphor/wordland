<?php
namespace WordLand;

class GeoLocation
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
        $this->lat = $lat;
    }

    public function setLong($long)
    {
        $this->long = $long;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
}
