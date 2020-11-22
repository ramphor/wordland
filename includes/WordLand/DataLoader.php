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
        if (!defined('DOING_AJAX')) {
            add_filter('posts_join', array($this, 'joinPropertiesTable'), 10, 2);
            add_filter('posts_fields', array($this, 'chooseFields'), 10, 2);
        }

        add_action('the_post', array($this, 'buildPropertyFromPost'));
    }

    protected function checkPostType($postTypes)
    {
        $allowedPostTypes = PostTypes::get();
        if (is_string($postTypes)) {
            return in_array($postTypes, $allowedPostTypes);
        }
        foreach ($postTypes as $postType) {
            if (in_array($postType, $allowedPostTypes)) {
                return true;
            }
        }
        return false;
    }

    public function joinPropertiesTable($join, $query)
    {
        if (!$this->checkPostType($query->query_vars['post_type'])) {
            return $join;
        }
        global $wpdb;
        $join .= " LEFT JOIN {$wpdb->prefix}wordland_properties wlp ON {$wpdb->posts}.ID=wlp.property_id";

        return $join;
    }

    public function chooseFields($fields, $query)
    {
        if (!$this->checkPostType($query->query_vars['post_type'])) {
            return $fields;
        }
        $fields .= ', wlp.property_id, wlp.price, wlp.bedrooms, wlp.bathrooms, wlp.unit_price, wlp.size, wlp.created_at';
        $fields .= ', ST_X(wlp.location) as latitude, ST_Y(wlp.location) as longitude';

        return $fields;
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
}
