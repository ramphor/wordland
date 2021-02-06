<?php
use WordLand\Template;
use WordLand\PostTypes;
use WordLand\Cache;
use WordLand\Query\LocationQuery;
use WordLand\Query\PropertyQuery;

function wordland_template($templates, $data = array(), $context = null, $echo = true)
{
    // Return the WordLand template render output or echo it
    return Template::render(
        $templates,
        $data,
        $context,
        $echo
    );
}


function is_property()
{
    return is_singular('property');
}

function is_wordland()
{
    if (is_singular('property')) {
        return true;
    } elseif (is_tax()) {
        $queried_object = get_queried_object();
        $wordland_taxonomies = get_object_taxonomies(PostTypes::PROPERTY_POST_TYPE);
        $wordland_taxonomies = apply_filters('wordland_taxonomies', $wordland_taxonomies);

        return in_array($queried_object->taxonomy, $wordland_taxonomies);
    }
    return false;
}

function wordland_post_thumbnail($size = 'wordland_thumbnail')
{
    $callable = apply_filters('wordland_post_thubmnail_callable', null);
    if (is_callable($callable)) {
        return call_user_func_array($callable, func_get_args());
    }
}

function wordland_get_current_location()
{
    $location = WordLand::instance()->location;
    if (is_null($location->current_location)) {
        $location->detect_location();
    }
    return $location->get_current_location();
}

function wordland_default_coordinates()
{
    return apply_filters(
        'wordland_map_center_geolocation',
        null
    );
}

function wordland_get_map_zoom($has_coordinates, $option_name = 'default_listing_properties')
{
    $default_marker_list_zoom = is_numeric($has_coordinates) ? $has_coordinates : 10;
    $marker_list_zoom = get_option("wordland_{$option_name}_map_zoom", $has_coordinates ? $default_marker_list_zoom : 6);
    $zoom_options = array(
        'single_property' => get_option('wordland_single_property_map_zoom', 14),
        'marker_list' => $marker_list_zoom
    );
    return apply_filters('wordland_get_map_zoom', $zoom_options);
}

function wordland_get_real_ip_address()
{
    $ip_headers = apply_filters('wordland_real_ip_headers', array(
        'HTTP_CF_IPCOUNTRY',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ));

    foreach ($ip_headers as $ip_header) {
        if (!empty($_SERVER[$ip_header])) {
            return $_SERVER[$ip_header];
        }
    }
    return '127.0.0.1';
}

function wordland_get_maxmind_license_key()
{
    if (defined('MAXMIND_API_KEY')) {
        return constant('MAXMIND_API_KEY');
    }
    return get_option('maxmind_api_key');
}

function wordland_check_property_is_viewed($property_id)
{
    if (Cache::checkViewed($property_id)) {
        return Cache::getViewed($property_id);
    }

    $counter   = WordLand::instance()->viewCounter;
    $is_viewed = $counter->isViewed($property_id);

    Cache::addViewed($property_id, $is_viewed);

    return $is_viewed;
}

function wordland_get_term_from_geo_location($point, $location_level = 1)
{
    if (empty($point)) {
        return false;
    }
    global $wpdb;
    $lat = array_get($point, 'lat');
    $lng = array_get($point, 'lng');
    $sql = $wpdb->prepare(
        "SELECT l.term_id, location_name, AsWKB(l.location) as kml from {$wpdb->prefix}wordland_locations l INNER JOIN {$wpdb->term_taxonomy} tt ON l.term_id=tt.term_id WHERE ST_CONTAINS(location, ST_GEOMFROMTEXT('POINT({$lng} {$lat})')) AND taxonomy=%s",
        apply_filters('wordland_find_geo_administrative_area_level', sprintf('administrative_area_level_%d', $location_level))
    );
    $term_location = $wpdb->get_row($sql);

    return $term_location;
}

function wordland_get_term_from_geo_name($name)
{
    global $wpdb;
    $sql = "SELECT l.term_id, location_name, AsWKB(l.location) as kml
        FROM {$wpdb->prefix}wordland_locations l
        WHERE
            `geo_eng_name` LIKE '%" . esc_sql($name) . "'";
    $term_location = $wpdb->get_row($sql);

    return $term_location;
}


function wordland_get_location_from_term($term_id)
{
    $location_query = new LocationQuery();
    return $location_query->query_location($term_id);
}

function wordland_get_same_location_properties_by_property_id($property_id, $listing_types = null, $select_total = false) {
    if (is_null($listing_types)) {
        $listing_types = wp_get_post_terms($property_id, PostTypes::PROPERTY_LISTING_TYPE, array(
            'fields' => 'ids'
        ));
    } elseif ($listing_types !== false) {
        $listing_types = array_filter((array)$listing_types);
    } else {
        $listing_types = array();
    }

    $args = array();
    if (!$select_total) {
        $args['post__not_in'] = array( $property_id );
    }

    if (count($listing_types) > 0) {
        $args['tax_query'][] = array(
            'taxonomy' => PostTypes::PROPERTY_LISTING_TYPE,
            'field' => 'term_id',
            'terms' => $listing_types,
            'operator' => 'IN',
            'hide_empty' => true,
        );
    }

    $propertyQuery = new PropertyQuery($args, 'detail');
    $propertyQuery->get_sample_location_properties($property_id);
    if ($select_total) {
        $propertyQuery->select_total_rows();
    }

    $wp_query               = $propertyQuery->getWordPressQuery();
    if ($select_total) {
        return intval($wp_query);
    }
    $sameLocationProperties = array();

    while($wp_query->have_posts()) {
        $wp_query->the_post();
        global $property;
        $sameLocationProperties[$wp_query->current_post] = $property;
    }

    return $sameLocationProperties;
}
