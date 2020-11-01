<?php
namespace WordLand;

use WordLand\Manager\PropertyBuilderManager;
use WordLand\Property;
use WordLand\GeoLocation;

class DataLoader
{
    private static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('the_post', array($this, 'buildPropertyFromPost'));
        add_action('the_post', array($this, 'setupSingleProperty'), 15, 2);
    }

    public function buildPropertyFromPost($post)
    {
        if ($post->post_type !== 'property') {
            return;
        }
        global $property;
        $queried_object = get_queried_object();
        if ($queried_object  === $post && (is_a($property, Property::class) && $property->ID === $post->ID)) {
            return;
        }
        $builder = PropertyBuilderManager::getBuilder();
        $builder->setPost($post);
        $builder->buildContent();
        $builder->build();

        $property = $builder->getProperty();

        return $GLOBALS['property'] = apply_filters(
            'wordland_build_property_data',
            $property,
            $builder
        );
    }

    public function buildPropertyFromId($propertyID)
    {
        $post = get_post($propertyID);
        return $this->buildPropertyFromPost($post);
    }

    // Create property data for single property page
    public function setupSingleProperty($post, $query)
    {
        if ($query->is_single()
            && in_array(
                $post->post_type,
                apply_filters('wordland_property_types', array('property'))
            )
        ) {
            global $property;
            static::createPropertyCustomData($post->ID, $property);
        }
    }

    public static function createPropertyCustomData($propertyID, &$property)
    {
        // When $proprety is not \WordLand\Property does't do any actions
        if (!is_a($property, Property::class)) {
            return;
        }

        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT wpro.ID, wpro.property_id, wpro.price, wpro.bedrooms, wpro.bathrooms,
                wpro.unit_price, wpro.size, ST_X(wpro.location) as latitude, ST_Y(wpro.location) as longitude
            FROM {$wpdb->prefix}wordland_properties wpro
            WHERE property_id=%d LIMIT 1",
            $propertyID,
        );
        $propertyData = $wpdb->get_row($sql);
        if ($propertyData) {
            $property->price = floatval($propertyData->price);
            $property->unitPrice = floatval($propertyData->unit_price);
            $property->size = floatval($propertyData->size);
            $property->bathrooms = intval($propertyData->bathrooms);
            $property->bedrooms = intval($propertyData->bedrooms);

            if (is_numeric($propertyData->latitude) || is_numeric($propertyData->longitude)) {
                $property->geolocation = new GeoLocation($propertyData->latitude, $propertyData->longitude);
            }
        }

        return $property;
    }
}
