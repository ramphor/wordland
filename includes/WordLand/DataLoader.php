<?php
namespace WordLand;

use WordLand\Manager\PropertyBuilderManager;
use WordLand\Property;

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
