<?php
namespace WordLand;

class PostTypes
{
    const PROPERTY_POST_TYPE = 'property';
    const PROPERTY_CATEGORY_TAX = 'property_cat';
    const PROPERTY_LOCATION_CITY_TAX = 'wordland_city';
    const PROPERTY_LOCATION_COUNTY_TAX = 'wordland_county';
    const PROPERTY_VISIBILITY = 'property_visibility';
    const PROPERTY_LISTING_TYPE = 'listing_type';

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

    public static function get()
    {
        return apply_filters(
            'wordland_property_types',
            array(
                static::PROPERTY_POST_TYPE
            )
        );
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
            static::PROPERTY_POST_TYPE,
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
         * Listing types
         *
         * Set listing type for rent, sale or sold
         */
        $listing_type_labels = array(
            'name' => __('Listing Type', 'wordland'),
        );
        register_taxonomy(
            static::PROPERTY_LISTING_TYPE,
            static::get(),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $listing_type_labels,
                    'public' => true,
                    'hierarchical' => true,
                    'show_admin_column' => true,
                )
            )
        );


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
            static::PROPERTY_CATEGORY_TAX,
            static::get(),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $category_labels,
                    'public' => true,
                    'hierarchical' => true,
                    'show_admin_column' => true,
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
            static::PROPERTY_VISIBILITY,
            static::get(),
            apply_filters(
                'wordland_taxonomy_category_args',
                array(
                    'labels' => $listing_type_labels,
                    'public' => true,
                    'hierarchical' => true,
                    'show_admin_column' => true,
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
            static::get(),
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
            static::get(),
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
