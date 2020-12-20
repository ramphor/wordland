<?php
namespace WordLand\Query;

class FilterHelper {
    public static function filterPrice($price, $is_unit_price = false) {
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
}
