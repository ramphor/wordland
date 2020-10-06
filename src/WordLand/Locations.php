<?php
/**
 * Register Location Taxonomy for Property
 *
 * Reference: https://en.wikipedia.org/wiki/List_of_administrative_divisions_by_country
 */
namespace WordLand;

class Locations
{
    private $levels = array();

    public function __construct()
    {
        add_action('init', array($this, 'registerLocationTaxonomies'));
    }

    public function registerLocationTaxonomies()
    {
        $labels = array(
            'name' => __('Cities', 'wordland'),
            'plural_name' => __('City', 'wordland'),
        );

        register_taxonomy(
            'location_first_level',
            PostTypes::PROPERTY_POST_TYPE,
            apply_filters('wordland_taxonomy_location_first_level_args', array(
                'labels' => $labels,
                'public' => true,
            ))
        );
    }
}
