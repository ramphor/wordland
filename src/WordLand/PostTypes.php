<?php
namespace WordLand;

class PostTypes
{
    public static $propertyPostType = 'public';

    public function __construct()
    {
        add_action('init', array( $this, 'registerPostStatuses' ));
        add_action('init', array( $this, 'registerPostTypes' ));
        add_action('init', array( $this, 'registerTaxonomies' ));
        add_action('init', array( $this, 'registerPropertytags' ), 50);
    }

    public function registerPostStatuses()
    {
    }

    public function registerPostTypes()
    {
        $labels = array(
            'name'         => __('Properties', 'wordland'),
            'plural_name'  => __('Property', 'wordland'),
            'add_new_item' => __('Add New Property', 'wordland'),
        );

        // Register the main post type of WordLand
        register_post_type(
            'property',
            apply_filters(
                'wordland_post_type_property_args',
                array(
                    'labels'   => $labels,
                    'public'   => true,
                    'supports' => array( 'title', 'editor', 'thumbnail' ),
                    'menu_icon' => 'dashicons-admin-multisite',
                )
            )
        );

        $labels = array(
            'name' => __('Amenities', 'wordland'),
            'plural_name' => __('Amenity', 'wordland')
        );

        // Register post type Amenities to manage
        register_post_type(
            'amenity',
            apply_filters('wordland_post_type_amenity_args', array(
                'labels' => $labels,
                'public' => true,
                'supports' => array('title', 'editor', 'thumbnail'),
                '_builtin' => true, // This post type use to manage amenity for property only
            ))
        );
    }

    public function registerTaxonomies()
    {
        /**
         * Property categories
         *
         * Use to create type houses, apartments, townhomes
         */
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
                    'hierarchical' => true,
                )
            )
        );

        /**
         * Listing types
         *
         * Set listing type for rent, sale or sold
         */
        $listing_type_labels = array(
            'name' => __('Listing Type', 'wordland'),
        );
        register_taxonomy(
            'listing_type',
            apply_filters('wordland_listing_type_post_types', array( 'property' )),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $listing_type_labels,
                    'public' => true,
                    'hierarchical' => true,
                )
            )
        );

        /**
         * Property Visibilities
         *
         * Visibilities use to create featured, hot, sold, new properties
         */
        $listing_type_labels = array(
            'name' => __('Visibility', 'wordland'),
        );
        register_taxonomy(
            'property_visibility',
            apply_filters('wordland_listing_type_post_types', array( 'property' )),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $listing_type_labels,
                    'public' => true,
                    'hierarchical' => true,
                )
            )
        );

        /**
         * Amenity Taxonomy
         *
         * Set category for amenity to create best UI for end users
         */
        $amenity_cat_labels = array(
            'name' => __('Amenities', 'wordland'),
            'plural_name' => __('Amenity', 'wordland')
        );
        register_taxonomy(
            'property_amenity',
            apply_filters('wordland_amenity_category_post_types', array( 'property' )),
            apply_filters(
                'wordland_property_amenity_args',
                array(
                    'labels' => $amenity_cat_labels,
                    'public' => false,
                    'hierarchical' => true,
                    '_builtin' => true, // The instead of this taxonomy is post type `amenity`
                )
            )
        );

        /**
         * Amenity Category
         *
         * Set category for amenity to create best UI for end users
         */
        $amenity_cat_labels = array(
            'name' => __('Groups', 'wordland'),
            'plural_name' => __('Group', 'wordland')
        );
        register_taxonomy(
            'amenity_cat',
            apply_filters('wordland_amenity_category_post_types', array( 'amenity' )),
            apply_filters(
                'wordland_amenity_category_args',
                array(
                    'labels' => $amenity_cat_labels,
                    'public' => true,
                    'hierarchical' => true,
                )
            )
        );

        do_action('wordland_register_taxonomies', $this);
    }

    public function registerPropertytags()
    {
        $tag_labels = array(
            'name'          => __('Property Tags', 'wordland'),
            'singular_name' => __('Property Tag', 'wordland'),
            'menu_name'     => __('Property Tags', 'wordland'),
        );

        register_taxonomy(
            'property_tag',
            apply_filters('wordland_tag_post_types', array( 'property' )),
            apply_filters(
                'wordland_taxonomy_tag_args',
                array(
                    'labels' => $tag_labels,
                    'public' => true,
                )
            )
        );

        do_action('wordland_register_property_tags', $this);
    }
}
