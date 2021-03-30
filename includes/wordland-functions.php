<?php
use WordLand\Template;
use WordLand\PostTypes;
use WordLand\Cache;
use WordLand\Property;
use WordLand\Query\LocationQuery;
use WordLand\Query\PropertyQuery;
use WordLand\Query\SearchHistoryQuery;

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

function wordland_get_option($option_name, $default_value = null)
{
    $pre = apply_filters("wordland_pre_get_{$option_name}_option", null, $option_name, $default_value);
    if (!is_null($pre)) {
        return $pre;
    }
    return get_option(sprintf('wordland_%s', $option_name), $default_value);
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
        'marker_list' => $marker_list_zoom,
        'country' => 6,
        'administrative_area_level_1' => 10,
        'administrative_area_level_2' => 12,
        'administrative_area_level_3' => 15,
        'administrative_area_level_4' => 20,
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

function wordland_get_location_from_name($name, $taxonomy, $parent = 0)
{
    $clean_name     = wordland_clean_location_name($name, true);
    $location_query = new LocationQuery();
    $filter_by_clean_name = function () {
        return array('clean_name' => 'like');
    };

    add_filter('wordland_query_location_where_columns', $filter_by_clean_name, 15);
    $terms = $location_query->query_location_by_keyword($clean_name, $taxonomy, $parent);
    remove_filter('wordland_query_location_where_columns', $filter_by_clean_name, 15);

    if (count($terms)) {
        return array_shift($terms);
    }
    return false;
}

function wordland_get_same_location_properties_by_property_id($property_id, $args = array(), $select_total = false)
{
    $args = wp_parse_args($args, array(
        'listing_types' => null,
        'exclude_current' => true
    ));
    $listing_types   = array_get($args, 'listing_types');
    $exclude_current = array_get($args, 'exclude_current');
    if (is_null($listing_types)) {
        $listing_types = wp_get_post_terms($property_id, PostTypes::PROPERTY_LISTING_TYPE, array(
            'fields' => 'ids'
        ));
    } elseif ($listing_types !== false) {
        $listing_types = array_filter((array)$listing_types);
    } else {
        $listing_types = array();
    }

    $args = array(
        'posts_per_page' => -1,
    );
    if ($exclude_current) {
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
    $propertyQuery->get_sample_location_properties($property_id, count($listing_types) > 0);
    if ($select_total) {
        $propertyQuery->select_total_rows();
    }

    $wp_query = $propertyQuery->getWordPressQuery();
    if ($select_total) {
        if ($wp_query->post_count && isset($wp_query->post->total_rows)) {
            return intval(intval($wp_query->post->total_rows));
        }
        return 0;
    }
    $sameLocationProperties = array();
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        global $property;
        if (is_a($property, Property::class)) {
            $property->makeCleanPriceHtml();
            $property->makeCleanUnitPriceHtml();
            $property->makeCleanAcreageHtml();

            $sameLocationProperties[$wp_query->current_post] = apply_filters(
                'wordland_setup_same_location_property',
                $property,
                $wp_query,
                $propertyQuery
            );
        }
    }

    return $sameLocationProperties;
}


function wordland_get_user_search_histories($user_id)
{
    $historyQuery = new SearchHistoryQuery();
    $rows         = $historyQuery->get_search_histories($user_id);

    if (is_null($rows)) {
        return [];
    }
    return $rows;
}


function wordland_get_map_zoom_from_location_taxonomy($taxonomy)
{
    if (preg_match('/\d{1,}$/', $taxonomy, $matches)) {
        $level = intval($matches[0]);
        if ($level > 3) {
            return wordland_get_map_zoom(20, 'location_level_4');
        } elseif ($level === 3) {
            return wordland_get_map_zoom(15, 'location_level_3');
        } elseif ($level === 2) {
            return wordland_get_map_zoom(12, 'location_level_3');
        } else {
            return wordland_get_map_zoom(10);
        }
    }
    return wordland_get_map_zoom(true);
    ;
}


function wordland_clean_location_name($name, $remove_unicode = false)
{
    return apply_filters(
        'wordland_clean_location_name',
        $name,
        $remove_unicode
    );
}

function wordland_validate_phone_number($phoneNumber)
{
    if (empty($phoneNumber)) {
        return false;
    }
    $phoneNumberRules = get_option('wordland_phone_number_format', false);

    $phoneIsOk = $phoneNumberRules
        ? preg_match(sprintf('/%s/', trim($phoneNumberRules, '/')), $phoneNumber)
        : strlen($phoneNumber) > 9 && strlen($phoneNumber) <= 15;

    return apply_filters(
        'wordland_validate_phone_number',
        $phoneIsOk,
        $phoneNumber,
        $phoneNumberRules
    );
}

function wordland_get_agent_type($user) {
    return 'agent';
}

function wordland_parse_agent_data($username) {
    $userType = 'agent';
    $user     = is_a($username, WP_User::class) ? $username : get_user_by('login', $username);

    return array(
        'user_type' => $userType,
        'agent_id' => $user->ID,
        'agent_name' => $user->display_name,
        'agent_email' => $user->user_email,
        'agent_description' => get_user_meta($user->ID, 'description', true),
        'agent' => &$user,
    );
}

function wordland_get_asset_url($path = '') {
    return sprintf(
        '%sassets/%s',
        plugin_dir_url(WORDLAND_PLUGIN_FILE),
        $path
    );
}

function wordland_go_to_search_url($saved_search) {
    $queryString = http_build_query(array(
        'searchQueryState' => $saved_search->search_content,
    ));

    return site_url('?' . $queryString);
}
