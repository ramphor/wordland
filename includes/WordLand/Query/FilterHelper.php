<?php
namespace WordLand\Query;

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
}
