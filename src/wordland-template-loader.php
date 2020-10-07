<?php
/******************************************
 ** Create property loop layout
 ******************************************/


 /******************************************
 ** Create property loop item content
 ******************************************/

function wordland_render_property_name()
{
    global $property;
    wordland_template('loop/property-name', array(
        'name' => $property->name,
        'link' => get_permalink(),
    ));
}
add_action('wordland_loop_property_name', 'wordland_render_property_name');

function wordand_render_property_thumbnail_image()
{
    wordland_template('loop/property-thumbnail', array(
        'thumbnail' => wordland_post_thumbnail(),
    ));
}
add_action('wordland_before_loop_property_name', 'wordand_render_property_thumbnail_image');
