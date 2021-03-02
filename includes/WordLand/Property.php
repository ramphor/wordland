<?php
namespace WordLand;

use WordLand\Abstracts\Data;
use Ramphor\FriendlyNumbers\Parser;
use Ramphor\FriendlyNumbers\Scale\CurrencyScale;
use Ramphor\FriendlyNumbers\Scale\AcreScale;
use Ramphor\FriendlyNumbers\Locale;

class Property extends Data
{
    public $ID;
    public $codeID;
    public $name;
    public $description;
    public $content;
    public $address;
    public $createdAt;
    public $price = 0;
    public $unit_price = 0;
    public $size = 0;
    public $bathroom = 0;
    public $bedrooms = 0;
    public $thumbnail = array();
    public $images = array();

    public $categories = array();
    public $types = array();
    public $visibilities = array();
    public $listingType = array();
    public $tags = array();

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
    public $primary_agent = null;

    public $marker_style = 'circle';

    public $listStyle;

    public $metas = array(
        'clean_price' => null,
        'clean_unit_price' => null,
        'clean_size' => null,
        'goto_detail' => null,
    );

    protected static $meta_fields = array(
        '%sproperty_id',
        '%saddress',
        '%sprice',
        '%sbedrooms',
        '%sbathrooms',
        '%sunit_price',
        '%ssize',
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
            $this->metas['clean_price'] = new Parser($this->price, new CurrencyScale(array(
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }


        $clean_price = array_get($this->metas, 'clean_price', null);
        if (!$clean_price) {
            return '';
        }
        $parsed = $clean_price->toArray();

        return $this->metas['clean_price'] = sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getCurrency())
        );
    }

    public function makeCleanUnitPriceHtml()
    {
        if (is_null($this->metas['clean_unit_price'])) {
            $this->metas['clean_unit_price'] = new Parser($this->unit_price, new CurrencyScale(array(
                'scale' => 'currency',
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }


        $clean_unit_price = array_get($this->metas, 'clean_unit_price', null);
        if (!$clean_unit_price) {
            return '';
        }
        $parsed = $clean_unit_price->toArray();

        return $this->metas['clean_unit_price'] = sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getCurrency())
        );
    }

    public function makeCleanSizeHtml()
    {
        if (is_null($this->metas['clean_size'])) {
            $this->metas['clean_size'] = new Parser($this->size, new AcreScale(array(
                'unit' => 'm2'
            )), new Locale(get_locale()));
        }

        $clean_size = array_get($this->metas, 'clean_size', null);
        if (!$clean_size) {
            return '';
        }
        $parsed = $clean_size->toArray();

        return $this->metas['clean_size'] = sprintf(
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

    public static function get_meta_fields($prefix = null, $get_location = false, $get_lat_lng = true)
    {
        $meta_fields = static::$meta_fields;
        if ($get_location) {
            array_push($meta_fields, '%slocation');
        }
        if ($get_lat_lng) {
            array_push($meta_fields, 'ST_X(%slocation) as latitude');
            array_push($meta_fields, 'ST_Y(%slocation) as longitude');
        }

        $prefix      = $prefix ? sprintf('%s.', $prefix) : '';
        $meta_fields = array_map(function ($field) use ($prefix) {
            return sprintf($field, $prefix);
        }, $meta_fields);

        return implode(', ', $meta_fields);
    }

    public function setSameLocationProperties($sameLocationProperties)
    {
        if (!is_array($sameLocationProperties)) {
            $this->sameLocationProperties = array();
            return;
        }
        $this->sameLocationProperties = $sameLocationProperties;
    }

    public function setSameLocationItems($numberOfItems)
    {
        $this->sameLocationItems = intval($numberOfItems);
    }
}
