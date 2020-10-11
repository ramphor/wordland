<?php
use WordLand\Template;
use Wordland\PostTypes;

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

function wordland_get_map_center_location()
{
    $geolocation = array();

    return apply_filters(
        'wordland_map_center_geolocation',
        $geolocation
    );
}

function wordland_get_map_zoom()
{
    return apply_filters('wordland_get_map_zoom', array(
        'single_property' => get_option('wordland_single_property_map_zoom', 14),
        'marker_list' => get_option('wordland_single_property_map_zoom', 10)
    ));
}
