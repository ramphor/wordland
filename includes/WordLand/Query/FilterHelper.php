<?php
namespace WordLand\Query;

use WordLand\PostTypes;

class FilterHelper
{
    /**
     * return array(
     *   from: number,
     *   to: number,
     *   between: boolean
     * )
     */
    public static function parsePrice($price)
    {
        $ret = array(
            'from' => 0,
            'to' => null,
            'between' => false
        );

        if (isset($price['from'])) {
            if (isset($price['from']['value'])) {
                $ret['from'] = $price['from']['value'];
            }
        }
        if (isset($price['to'])) {
            if (isset($price['to']['value'])) {
                $ret['to'] = $price['to']['value'];
            }
        }

        if (!$ret['from'] && !$ret['to']) {
            return false;
        }

        $ret['between'] = $ret['from'] && $ret['to'];

        return $ret;
    }

    /**
     * return array(
     *   from: number,
     *   to: number,
     *   between: boolean
     * )
     */
    public static function parseSize($size)
    {
        $ret = array(
            'from' => 0,
            'to' => null,
            'between' => false
        );

        if (isset($size['from'])) {
            if (isset($size['from']['value'])) {
                $ret['from'] = $size['from']['value'];
            }
        }
        if (isset($size['to'])) {
            if (isset($size['to']['value'])) {
                $ret['to'] = $size['to']['value'];
            }
        }

        if (!$ret['from'] && !$ret['to']) {
            return false;
        }

        $ret['between'] = $ret['from'] && $ret['to'];

        return $ret;
    }

    public static function filterPrice($price, $is_unit_price = false)
    {
        global $wpdb;
        $field_name = $is_unit_price ? 'w.unit_price' : 'w.price';

        // column_name BETWEEN value1 AND value2
        if (array_get($price, 'between')) {
            return $wpdb->prepare("{$field_name} BETWEEN %f AND %f", $price['from'], $price['to']);
        } elseif (empty($price['to'])) {
            return $wpdb->prepare("{$field_name} >= %f", $price['from']);
        } else {
            return$wpdb->prepare("{$field_name} <= %f", $price['to']);
        }
    }

    public static function filterSize($price)
    {
        global $wpdb;
        // column_name BETWEEN value1 AND value2
        if (array_get($price, 'between')) {
            return $wpdb->prepare("w.size BETWEEN %f AND %f", $price['from'], $price['to']);
        } elseif (empty($price['to'])) {
            return $wpdb->prepare("w.size >= %f", $price['from']);
        } else {
            return$wpdb->prepare("w.size <= %f", $price['to']);
        }
    }

    public static function parseListingType($listingType)
    {
        if (isset($listingType['id'])) {
            return array(
                'taxonomy' => isset($listingType['taxonomy']) ? $listingType['taxonomy'] : PostTypes::PROPERTY_LISTING_TYPE,
                'field' => 'term_id',
                'terms' => intval($listingType['id'])
            );
        }
        return false;
    }

    public static function parseBedsroom($bedsroom)
    {
        if (empty($bedsroom['room'])) {
            return false;
        }
        global $wpdb;

        $exactly = isset($bedsroom['exactly']) ? boolval($bedsroom['exactly']): false;
        if ($exactly) {
            return $wpdb->prepare(" AND w.bedrooms = %d", $bedsroom['room']);
        }
        return $wpdb->prepare(" AND w.bedrooms >= %d", $bedsroom['room']);
    }

    public static function parseBathsroom($bathroom)
    {
        if (empty($bathroom['room'])) {
            return false;
        }
        global $wpdb;
        return $wpdb->prepare(" AND w.bathrooms >= %d", $bathroom['room']);
    }

    public static function parseMapBounds($map_bounds)
    {
        if (empty($map_bounds['bounds']) || empty($map_bounds['bounds']['north_east']) || empty($map_bounds['bounds']['south_west'])) {
            return false;
        }
        global $wpdb;

        $north_east = $map_bounds['bounds']['north_east'];
        $south_west = $map_bounds['bounds']['south_west'];

        $north = array_get($north_east, 'lat');
        $east  = array_get($north_east, 'lng');
        $south = array_get($south_west, 'lat');
        $west  = array_get($south_west, 'lng');

        return " AND ST_Contains(
            ST_PolygonFromText('POLYGON(
                ({$north} {$west}, {$north} {$east}, {$south} {$east}, {$south} {$west}, {$north} {$west})
            )'),
            w.location
        )";
    }
}
