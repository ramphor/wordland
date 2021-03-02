<?php
namespace WordLand\Query;

class LocationQuery
{
    protected function create_query_sql($keyword, $taxonomy = null, $parent = 0)
    {
        global $wpdb;
        $columns = apply_filters('wordland_query_location_where_columns', array(
            'location_name' => 'LIKE',
            'ascii_name' => 'LIKE',
            'clean_name' => 'LIKE',
            'zip_code' => '='
        ));

        $sql = "SELECT DISTINCT {$wpdb->prefix}wordland_locations.term_id, location_name, zip_code, {$wpdb->term_taxonomy}.taxonomy
            FROM {$wpdb->prefix}wordland_locations
            INNER JOIN {$wpdb->term_taxonomy}
                ON {$wpdb->term_taxonomy}.term_id={$wpdb->prefix}wordland_locations.term_id
            WHERE";
        $where = ' (';
        foreach ($columns as $column => $operator) {
            $where .= sprintf(
                ' `%s` %s \'%s\' OR ',
                $column,
                $operator,
                strtolower($operator) == 'like' ? '%' . $wpdb->_real_escape($keyword) . '%' : $wpdb->_real_escape($keyword)
            );
        }
        $sql .= $where;
        $sql  = rtrim($sql, ' OR ') . ')';

        if ($taxonomy) {
            $sql .= $wpdb->prepare(" AND {$wpdb->term_taxonomy}.taxonomy=%s", $taxonomy);
        }
        if ($parent > 0) {
            $sql .= $wpdb->prepare(" AND {$wpdb->term_taxonomy}.parent=%d", $parent);
        }

        $orderby = apply_filters('wordland_query_location_orderby', 'ORDER BY LENGTH(location_name) ASC');
        if ($orderby) {
            $sql .= sprintf(' %s', $orderby);
        }

        $limit = apply_filters('wordland_query_location_limit_items', 5);
        if ($limit) {
            $sql .= sprintf(' LIMIT %d', $limit);
        }

        return apply_filters('wordland_get_query_location_sql', $sql);
    }

    protected function query($keyword, $taxonomy = null, $parent = 0)
    {
        global $wpdb;

        $sql = $this->create_query_sql($keyword, $taxonomy, $parent);

        // All locations in database
        return $wpdb->get_results($sql);
    }

    public function query_location_by_keyword($keyword, $taxonomy = null, $parent = 0)
    {
        $locations = array();

        $query_results = $this->query($keyword, $taxonomy, $parent);

        foreach ($query_results as $query_result) {
            $locations[$query_result->term_id] = array(
                'term_id' => intval($query_result->term_id),
                'name' => $query_result->location_name,
                'zipcode' => $query_result->zip_code,
                'taxonomy' => $query_result->taxonomy
            );
        }

        return apply_filters(
            'wordland_query_location_results',
            $locations
        );
    }

    public function query_location($term_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT location_name, AsWKB(location) as location, {$wpdb->term_taxonomy}.taxonomy
            FROM {$wpdb->prefix}wordland_locations
            INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->prefix}wordland_locations.term_id = {$wpdb->term_taxonomy}.term_id
            WHERE {$wpdb->prefix}wordland_locations.term_id=%d",
            $term_id
        );

        $location = $wpdb->get_row(apply_filters('wordland_query_single_location_sql', $sql));
        if (is_null($location)) {
            return false;
        }
        return $location;
    }
}
