<?php
use WordLand\Template;

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
    }
    return false;
}

function wordland_post_thumbnail($size = 'wordland_thumbnail') {
    $callable = apply_filters('wordland_post_thubmnail_callable', null);
    if (is_callable($callable)) {
        return call_user_func_array($callable, func_get_args());
    }
}
