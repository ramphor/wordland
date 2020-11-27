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

    public function get_maxmind_location()
    {
        return $this->maxmind_geolocation;
    }
}
