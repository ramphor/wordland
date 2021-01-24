<?php
/**
 * Register Location Taxonomy for Property
 *
 * Reference: https://en.wikipedia.org/wiki/List_of_administrative_divisions_by_country
 */
namespace WordLand;

use WordLand\Integrations\MaxMind\GeoLocation;

class Locations
{
    private $levels = array();
    public $current_location;
    protected $source_name;
    protected $locator;
    protected $location_sources;

    public function __construct()
    {
        add_action('after_setup_theme', array($this, 'load_location_sources'), 20);
        add_action('init', array($this, 'registerLocationTaxonomies'));
        add_action('init', array($this, 'detect_location'));
    }

    public function load_location_sources()
    {
        $this->source_name = get_option('wordland_location_source', 'company_base_location');
        $this->maxmind_geolocation = new GeoLocation();
        $this->location_sources = apply_filters('wordland_location_sources', array(
            'company_base_location' => array($this, 'get_company_base_location'),
            GeoLocation::SOURCE_NAME => array($this->maxmind_geolocation, 'get_current_location')
        ));
    }

    public function detect_location()
    {
        if (isset($this->location_sources[$this->source_name])) {
            $source = $this->location_sources[$this->source_name];
            if (is_callable($source)) {
                $this->current_location = call_user_func($source);
            } elseif (is_array($source) && isset($source['callable']) && $source['args']) {
                $this->current_location = call_user_func_array(
                    $source['callable'],
                    $source['args']
                );
            }
        }
    }

    public function get_company_base_location()
    {
        $geolocation = array();
        return $geolocation;
    }

    public function get_source_name()
    {
        return $this->source_name;
    }

    public function get_current_location()
    {
        return apply_filters(
            'wordland_current_location',
            $this->current_location
        );
    }

    public function registerLocationTaxonomies()
    {
        if (apply_filters('wordland_enable_country_taxonomy', false)) {
            $labels = array(
                'name' => __('Countries', 'wordland'),
                'plural_name' => __('Country', 'wordland'),
            );
            register_taxonomy(
                'country',
                PostTypes::get(),
                apply_filters('wordland_taxonomy_country_args', array(
                    'labels' => $labels,
                    'public' => true,
                    'hierarchical' => true,
                ))
            );
        }

        $labels = array(
            'name' => __('Areas Level 1', 'wordland'),
            'plural_name' => __('Area Level 1', 'wordland'),
        );

        register_taxonomy(
            'administrative_area_level_1',
            PostTypes::get(),
            apply_filters('wordland_taxonomy_administrative_area_level_1_args', array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
            ))
        );

        $labels = array(
            'name' => __('Areas Level 2', 'wordland'),
            'plural_name' => __('Area Level 2', 'wordland'),
        );
        register_taxonomy(
            'administrative_area_level_2',
            PostTypes::get(),
            apply_filters('wordland_taxonomy_administrative_area_level_2_args', array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
            ))
        );

        $labels = array(
            'name' => __('Areas Level 3', 'wordland'),
            'plural_name' => __('Area Level 3', 'wordland'),
        );
        register_taxonomy(
            'administrative_area_level_3',
            PostTypes::get(),
            apply_filters('wordland_taxonomy_administrative_area_level_3_args', array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => true,
            ))
        );

        if (apply_filters('wordland_enable_area_level_4', false)) {
            $labels = array(
                'name' => __('Areas Level 4', 'wordland'),
                'plural_name' => __('Area Level 4', 'wordland'),
            );
            register_taxonomy(
                'administrative_area_level_4',
                PostTypes::get(),
                apply_filters('wordland_taxonomy_administrative_area_level_4_args', array(
                    'labels' => $labels,
                    'public' => true,
                    'hierarchical' => true,
                ))
            );
        }
    }

    public function get_maxmind_location()
    {
        return $this->maxmind_geolocation;
    }
}
