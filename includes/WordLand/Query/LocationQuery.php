<?php
namespace WordLand\Query;

class LocationQuery
{
    public function query_location_by_keyword($keyword)
    {
        $locations = array();

        return apply_filters(
            'wordland_query_location_results',
            $locations
        );
    }
}
