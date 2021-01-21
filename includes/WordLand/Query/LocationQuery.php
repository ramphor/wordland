<?php
namespace WordLand\Query;

class LocationQuery
{
    protected function create_query_sql($keyword) {
        global $wpdb;
        $columns = apply_filters('wordland_query_location_where_columns', array(
            'location_name' => 'LIKE',
            'ascii_name' => 'LIKE',
            'zip_code' => '='
        ));


        $sql = "SELECT term_id, location_name, zip_code FROM {$wpdb->prefix}wordland_locations WHERE";
        foreach($columns as $column => $operator) {
            $sql .= sprintf(
                ' `%s` %s \'%s\' OR ',
                $column,
                $operator,
                esc_sql(strtolower($operator) == 'like' ? '%' . $keyword . '%' : $keyword)
            );
        }
        $sql = rtrim($sql, ' OR ');
        $orderby = apply_filters('wordland_query_location_orderby', 'ORDER BY LENGTH(location_name) ASC');
        if ($orderby) {
            $sql .= sprintf(' %s', $orderby);
        }

        $limit = apply_filters('wordland_query_location_limit_items', 5);
        if ($limit) {
            $sql .= sprintf(' LIMIT %d', $limit);
        }

        return apply_filters( 'wordlan_get_query_location_sql', $sql );
    }

    protected function query($keyword) {
        global $wpdb;

        $sql     = $this->create_query_sql($keyword);

        // All locations in database
        return $wpdb->get_results($sql);
    }

    public function query_location_by_keyword($keyword)
    {
        $locations = array();

        $query_results = $this->query($keyword);
        foreach($query_results as $query_result) {
            $locations[$query_result->term_id] = array(
                'term_id' => intval($query_result->term_id),
                'name' => $query_result->location_name,
                'zipcode' => $query_result->zip_code
            );
        }

        return apply_filters(
            'wordland_query_location_results',
            $locations
        );
    }
}
