<?php
namespace WordLand\Query;

use geoPHP;
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
    public static function parseAcreage($acreage)
    {
        $ret = array(
            'from' => 0,
            'to' => null,
            'between' => false
        );

        if (isset($acreage['from'])) {
            if (isset($acreage['from']['value'])) {
                $ret['from'] = $acreage['from']['value'];
            }
        }
        if (isset($acreage['to'])) {
            if (isset($acreage['to']['value'])) {
                $ret['to'] = $acreage['to']['value'];
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
        $field_name = $is_unit_price
            ? " {$wpdb->prefix}wordland_properties.unit_price"
            : " {$wpdb->prefix}wordland_properties.price";

        // column_name BETWEEN value1 AND value2
        if (array_get($price, 'between')) {
            return $wpdb->prepare("{$field_name} BETWEEN %f AND %f", $price['from'], $price['to']);
        } elseif (empty($price['to'])) {
            return $wpdb->prepare("{$field_name} >= %f", $price['from']);
        } else {
            return$wpdb->prepare("{$field_name} <= %f", $price['to']);
        }
    }

    public static function filterAcreage($price)
    {
        global $wpdb;
        // column_name BETWEEN value1 AND value2
        if (array_get($price, 'between')) {
            return $wpdb->prepare(" {$wpdb->prefix}wordland_properties.acreage BETWEEN %f AND %f", $price['from'], $price['to']);
        } elseif (empty($price['to'])) {
            return $wpdb->prepare(" {$wpdb->prefix}wordland_properties.acreage >= %f", $price['from']);
        } else {
            return$wpdb->prepare(" {$wpdb->prefix}wordland_properties.acreage <= %f", $price['to']);
        }
    }

    public static function parseListingType($listingType)
    {
        if ($listingType > 0) {
            return array(
                'taxonomy' => PostTypes::PROPERTY_LISTING_TYPE,
                'field' => 'term_id',
                'terms' => intval($listingType)
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
            return $wpdb->prepare(" AND {$wpdb->prefix}wordland_properties.bedrooms = %d", $bedsroom['room']);
        }
        return $wpdb->prepare(" AND {$wpdb->prefix}wordland_properties.bedrooms >= %d", $bedsroom['room']);
    }

    public static function parseBathsroom($bathroom)
    {
        if (empty($bathroom['room'])) {
            return false;
        }
        global $wpdb;
        return $wpdb->prepare(" AND {$wpdb->prefix}wordland_properties.bathrooms >= %d", $bathroom['room']);
    }

    public static function parseMapBounds($map_bounds)
    {
        if (empty($map_bounds) || empty($map_bounds['northEast']) || empty($map_bounds['southWest'])) {
            return false;
        }
        global $wpdb;

        $northEast = $map_bounds['northEast'];
        $southWest = $map_bounds['southWest'];

        $north = array_get($northEast, 'lat');
        $east  = array_get($northEast, 'lng');
        $south = array_get($southWest, 'lat');
        $west  = array_get($southWest, 'lng');

        return " AND ST_Contains(
            ST_PolygonFromText('POLYGON(
                ({$north} {$west}, {$north} {$east}, {$south} {$east}, {$south} {$west}, {$north} {$west})
            )'),
            {$wpdb->prefix}wordland_properties.coordinate
        )";
    }

    public static function parseLocation($location)
    {
        if (isset($location['term_id']) && ($term = get_term($location['term_id']))) {
            return array(
                'taxonomy' => $term->taxonomy,
                'field' => 'term_id',
                'terms' => $term->term_id,
                'include_children' => true,
                'operator' => 'IN'
            );
        }
    }

    public static function parseDays($days)
    {
        if (is_numeric($days)) {
            return intval($days);
        }
        return 0;
    }


    public static function parseMapRadius($radius, $map_bounds)
    {
        global $wpdb;

        $northEast = $map_bounds['northEast'];
        $southWest = $map_bounds['southWest'];

        $json = sprintf(
            '{
                "type": "MultiPoint",
                "coordinates": [
                    [%s, %s], [%s, %s]
                ]
            }',
            array_get($northEast, 'lat'),
            array_get($northEast, 'lng'),
            array_get($southWest, 'lat'),
            array_get($southWest, 'lng')
        );

        $multipoint = geoPHP::load($json, 'json');
        $centroid = $multipoint->getCentroid();

        if ($centroid->isEmpty()) {
            return false;
        }

        return sprintf(
            "SQRT(POW(X({$wpdb->prefix}wordland_properties.coordinate) - %s , 2) + POW(Y({$wpdb->prefix}wordland_properties.coordinate) - %s, 2)) * 100 < %d",
            $centroid->getX(),
            $centroid->getY(),
            $radius
        );
    }
}
