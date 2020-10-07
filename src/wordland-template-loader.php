<?php
/******************************************
 ** Create property loop layout
 ******************************************/
function wordland_before_loop_wrapper_open()
{
    $columns = apply_filters('wordland_property_list_columns', 4);
    $loop_wrap_attributes = array(
        'class' => apply_filters('wordland_loop_wrapper_classes', array(
            'wordland-list',
            'columns-' . $columns,
        ))
    );
    wordland_template('loop/loop-start', array(
        'attributes' => jankx_generate_html_attributes($loop_wrap_attributes)
    ));
}
add_action('wordland_before_loop', 'wordland_before_loop_wrapper_open');

function wordland_end_loop_wrapper_close()
{
    wordland_template('loop/loop-end');
}
add_action('wordland_end_loop', 'wordland_end_loop_wrapper_close');


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
