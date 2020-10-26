<?php
use WordLand\Cache;

/******************************************
 ** Create property loop layout
 ******************************************/
function wordland_before_loop_wrapper_open($args = array())
{
    $args = wp_parse_args($args, array(
        'columns' => 4,
    ));
    $columns = apply_filters('wordland_property_list_columns', $args['columns']);
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
// Start layout
function wordland_open_property_featured_block() {
    ?>
    <div class="featured-block">
    <?php
}
add_action('wordland_before_loop_property', 'wordland_open_property_featured_block', 5);

function wordland_close_property_featured_block() {
    echo '</div>';
}
add_action('wordland_before_loop_property_name', 'wordland_close_property_featured_block', 15);


function wordland_open_property_other_features() {
    ?>
    <div class="other-features">
    <?php
}
add_action('wordland_before_loop_property_name', 'wordland_open_property_other_features', 20);

function wordland_close_property_other_features() {
    echo '</div>';
}
add_action('wordland_after_loop_property', 'wordland_close_property_other_features', 35);
// End layout

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
        'thumbnail' => wordland_post_thumbnail('medium'),
    ));
}
add_action('wordland_before_loop_property_name', 'wordand_render_property_thumbnail_image');


function wordland_render_property_metas()
{
    $supported_metas = Cache::getPropertyMetas();
    if (empty($supported_metas)) {
        return;
    }

    global $property;

    wordland_template('loop/meta-open-wrapper', array());
    foreach ($supported_metas as $meta => $label) {
        wordland_template("loop/meta/{$meta}", array(
            'meta_value' => $property->getMeta($meta),
            'label' => $label
        ));
    }
    wordland_template('loop/meta-close-wrapper', array());
}
add_action('wordland_after_loop_property_name', 'wordland_render_property_metas');


function wordland_render_property_footer_items()
{
    $footerItems = Cache::getPropertyFooterItems();
    if (empty($footerItems)) {
        return;
    }
    wordland_template('loop/footer-open-wrapper', array());
    foreach ($footerItems as $key => $args) {
        $data = array(
            'args' => $args,
        );
        if ($key === 'user_info') {
            $data['user'] = get_user_by( 'ID', get_the_author_meta('ID') );
            if (is_a($data['user'], \WP_User::class)) {
                // Remove secure user infos
                unset(
                    $data['user']->data->user_pass,
                    $data['user']->data->user_email
                );
            }
        }
        wordland_template('loop/footer/' . $key, $data);
    }
    wordland_template('loop/footer-close-wrapper', array());
}
add_action('wordland_after_loop_property', 'wordland_render_property_footer_items');

function wordland_property_user_action_share($label)
{
    wordland_template('loop/footer/share', array(
        'label' => $label,
    ));
}
add_action('wordland_property_user_action_share', 'wordland_property_user_action_share');


function wordland_property_user_action_favorite($label)
{
    wordland_template('loop/footer/favorite', array(
        'label' => $label,
    ));
}
add_action('wordland_property_user_action_favorite', 'wordland_property_user_action_favorite');
