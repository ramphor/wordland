<?php
namespace WordLand;

use Ramphor\FriendlyNumbers\Parser;
use Ramphor\FriendlyNumbers\Scale\CurrencyScale;
use Ramphor\FriendlyNumbers\Scale\MetricScale;
use Ramphor\FriendlyNumbers\Locale;

use WordLand\Abstracts\Data;
use WordLand\Locations;

class Property extends Data
{
    public $ID;
    public $codeID;
    public $sku;
    public $name;
    public $description;
    public $content;
    public $address;
    public $fullAddress;
    public $listingTypeId;
    public $createdAt;
    public $url;
    public $videoUrl;
    public $price = 0;
    public $unitPrice = 0;
    public $acreage = 0;
    public $bathroom = 0;
    public $bedrooms = 0;
    public $frontWidth = 0;
    public $roadWidth = 0;
    public $images = array();

    protected $areaLevelId1;
    protected $areaLevelId2;
    protected $areaLevelId3;
    protected $areaLevelId4;
    protected $countryId;

    // Location objects
    protected $areaLevel1;
    protected $areaLevel2;
    protected $areaLevel3;
    protected $areaLevel4;
    protected $country;

    public $categories = array();
    public $types = array();
    public $visibilities = array();
    public $listingType = array();

    public $tags = array();

    /**
     * The property location
     *
     * @var \WordLand\Coordinate
     */
    public $coordinate = null;

    /**
     * Property agent
     *
     * @var \WordLand\Agent;
     */
    public $primaryAgent = null;
    public $agents = array();

    public $marker_style = 'circle';

    public $listStyle;

    public $metas = array(
        'clean_price' => null,
        'clean_unit_price' => null,
        'clean_acreage' => null,
        'goto_detail' => null,
    );

    protected static $meta_fields = array(
        'property_id',
        'sku',
        'address',
        'full_address',
        'price',
        'bedrooms',
        'bathrooms',
        'unit_price',
        'acreage',
        'front_width',
        'road_width',
        'listing_type',
        'total_views'
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
            case 'clean_acreage':
                return $this->makeCleanAcreageHtml();
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

    public function getAcreageUnit()
    {
        return 'm2';
    }

    public function makeCleanPriceHtml($price = null)
    {
        if (is_null($this->metas['clean_price'])) {
            if (is_null($price)) {
                $price = $this->price;
            }
            $this->metas['clean_price'] = new Parser($price, new CurrencyScale(array(
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

    public function makeCleanUnitPriceHtml($price = null)
    {
        if (is_null($this->metas['clean_unit_price'])) {
            if (is_null($price)) {
                $price = $this->unit_price;
            }
            $this->metas['clean_unit_price'] = new Parser($price, new CurrencyScale(array(
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

    public function makeCleanAcreageHtml($acreage = null)
    {
        if (is_null($this->metas['clean_acreage'])) {
            if (is_null($acreage)) {
                $acreage = $this->acreage;
            }
            $this->metas['clean_acreage'] = new Parser($acreage, new MetricScale(array(
                'unit' => 'm2'
            )), new Locale(get_locale()));
        }

        $clean_acreage = array_get($this->metas, 'clean_acreage', null);
        if (!$clean_acreage) {
            return '';
        }
        $parsed = $clean_acreage->toArray();

        return $this->metas['clean_acreage'] = sprintf(
            '<span class="val">%s</span> <span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'prefix', $this->getAcreageUnit())
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

    public static function get_meta_fields($prefix = null, $array_results = false, $get_lat_lng = true, $get_location = false)
    {
        $meta_fields = static::$meta_fields;
        if ($get_location) {
            array_push($meta_fields, 'location');
        }

        $prefix      = $prefix ? sprintf('%s.', $prefix) : '';
        $meta_fields = array_map(function ($field) use ($prefix) {
            return $prefix . $field;
        }, $meta_fields);

        if ($get_lat_lng) {
            array_push($meta_fields, sprintf('ST_X(%scoordinate) as latitude', $prefix));
            array_push($meta_fields, sprintf('ST_Y(%scoordinate) as longitude', $prefix));
        }

        if ($array_results) {
            return $meta_fields;
        }

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

    public function setLocationByLevel($value, $level = 'country')
    {
        $mapTos = array(
            Locations::COUNTRY_LEVEL => 'country',
            Locations::BOUNDARY_LEVEL_1 => 'areaLevel1',
            Locations::BOUNDARY_LEVEL_2 => 'areaLevel2',
            Locations::BOUNDARY_LEVEL_3 => 'areaLevel3',
            Locations::BOUNDARY_LEVEL_4 => 'areaLevel4',
        );
        $mapKey = $mapTos[$level];
        $this->$mapKey = $value;
    }
}
