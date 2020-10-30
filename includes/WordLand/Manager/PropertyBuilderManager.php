<?php
namespace WordLand\Manager;

use WordLand\Builder\PropertyBuilder;
use WordLand\Constracts\PropertyBuilder as PropertyBuilderConstract;

class PropertyBuilderManager
{
    protected static $propertyBuilder;

    public static function getBuilder($post = null)
    {
        if (is_null($post)) {
            $post = $GLOBALS['post'];
        }
        $builderClass = apply_filters('wordland_get_property_builder', PropertyBuilder::class, $post);
        $builder = new $builderClass($post);
        if (is_a($builder, PropertyBuilderConstract::class)) {
            static::$propertyBuilder = $builder;
        } else {
            static::$propertyBuilder = new PropertyBuilder($post);
        }
        return static::$propertyBuilder;
    }
}
