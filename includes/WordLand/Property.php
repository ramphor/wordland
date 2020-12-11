<?php
namespace WordLand;

use JsonSerializable;
use ReflectionObject;
use ReflectionProperty;
use WordLand\Abstracts\Data;
use Ramphor\FriendlyNumbers\Parser;
use Ramphor\FriendlyNumbers\Scale;
use Ramphor\FriendlyNumbers\Locale;

class Property extends Data implements JsonSerializable
{
    public $ID;
    public $name;
    public $description;
    public $content;
    public $address;
    public $price = 0;
    public $unitPrice = 0;
    public $size = 0;
    public $bathroom = 0;
    public $bedrooms = 0;
    public $images = array();

    public $categories = array();
    public $types = array();
    public $visibilities = array();

    /**
     * The property location
     *
     * @var \WordLand\GeoLocation
     */
    public $geolocation = null;

    /**
     * Property agent
     *
     * @var \WordLand\Agent;
     */
    public $primaryAgent = null;

    public $markerStyle = 'circle';

    public $listStyle;

    public $metas = array(
        'clean_price' => null,
        'clean_unit_price' => null,
        'clean_size' => null,
        'goto_detail' => null,
    );


    public function setMeta($key, $value)
    {
        $this->metas[$key] = $value;
    }

    public function getMeta($key, $defaultValue = null)
    {
        switch ($key) {
            case 'clean_price':
                return $this->makeCleanPriceHtml();
            case 'clean_size':
                return $this->makeCleanSizeHtml();
            case 'goto_detail':
                return get_permalink();
            default:
                if (isset($this->metas[$key])) {
                    return $this->metas[$key];
                }
                return $defaultValue;
        }
    }

    public function is_visible()
    {
        return true;
    }

    public function getCurrency()
    {
        return 'đ';
    }

    public function getSizeUnit()
    {
        return 'm2';
    }

    public function makeCleanPriceHtml()
    {
        if (is_null($this->metas['clean_price'])) {
            $this->metas['clean_price'] = new Parser($this->price, new Scale(array(
                'scale' => 'currency',
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }


        $clean_price = array_get($this->metas, 'clean_price', null);
        if (!$clean_price) {
            return '';
        }
        $parsed = $clean_price->toArray();

        return sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getCurrency())
        );
    }

    public function makeCleanUnitPriceHtml()
    {
        if (is_null($this->metas['clean_unit_price'])) {
            $this->metas['clean_unit_price'] = new Parser($this->unit_price, new Scale(array(
                'scale' => 'currency',
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }


        $clean_unit_price = array_get($this->metas, 'clean_unit_price', null);
        if (!$clean_unit_price) {
            return '';
        }
        $parsed = $clean_unit_price->toArray();

        return sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getCurrency())
        );
    }

    public function makeCleanSizeHtml()
    {
        if (is_null($this->metas['clean_size'])) {
            $this->metas['clean_size'] = new Parser($this->size, new Scale(array(
                'scale' => 'metric',
                'unit' => 'm2'
            )), new Locale(get_locale()));
        }

        $clean_size = array_get($this->metas, 'clean_size', null);
        if (!$clean_size) {
            return '';
        }
        $parsed = $clean_size->toArray();

        return sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getSizeUnit())
        );
    }

    public function setListStyle($style)
    {
        $this->listStyle = $style;
    }

    public function getListStyle()
    {
        return $this->listStyle;
    }

    public function jsonSerialize()
    {
        $data        = array();
        $propertyRef = new ReflectionObject($this);
        $properties  = $propertyRef->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            $propertyName = $property->name;
            $key = preg_replace_callback('/([a-z0-9])([A-Z])/', function($matches){
                return sprintf('%s_%s', $matches[1], $matches[2]);
            }, $propertyName);
            if ($key !== 'ID') {
                $key = strtolower($key);
            }
            $data[$key] = $this->$propertyName;
        }

        return apply_filters('wordland_property_supported_json_fields', $data, $this);
    }
}
