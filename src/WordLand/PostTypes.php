<?php
namespace WordLand;

class PostTypes
{
    public function __construct()
    {
        add_action('init', array( $this, 'register_post_statuses' ));
        add_action('init', array( $this, 'register_post_types' ));
        add_action('init', array( $this, 'register_taxonomies' ));
    }

    public function register_post_statuses()
    {
    }

    public function register_post_types()
    {
        $labels = array(
            'name'         => __('Properties', 'wordland'),
            'plural_name'  => __('Property', 'wordland'),
            'add_new_item' => __('Add New Property', 'wordland'),
        );

        register_post_type(
            'property',
            apply_filters(
                'wordland_post_type_property_args',
                array(
                    'labels'   => $labels,
                    'public'   => true,
                    'supports' => array( 'title', 'editor' ),
                )
            )
        );
    }

    public function register_taxonomies()
    {
        $category_labels = array(
            'name'          => __('Categories', 'wordland'),
            'singular_name' => __('Category', 'wordland'),
            'menu_name'     => __('Categories', 'wordland'),
        );

        register_taxonomy(
            'property_cat',
            apply_filters('wordland_category_post_types', array( 'property' )),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $category_labels,
                    'public' => true,
                )
            )
        );

        do_action('wordland_register_taxonomies', $this);
    }
}
