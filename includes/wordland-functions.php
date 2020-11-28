<?php
use WordLand\Template;
use WordLand\PostTypes;

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

function wordland_get_map_zoom($has_coordinates)
{
    return apply_filters('wordland_get_map_zoom', array(
        'single_property' => get_option('wordland_single_property_map_zoom', 14),
        'marker_list' => get_option('wordland_single_property_map_zoom', $has_coordinates ? 9 : 6)
    ));
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
