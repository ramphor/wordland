<?php
namespace WordLand\Manager;

use WordLand\Builder\PropertyBuilder;
use WordLand\Constracts\DataBuilder;

class PropertyBuilderManager
{
    protected static $propertyBuilder = PropertyBuilder::class;

    public static function getBuilder($post = null)
    {
        if (is_null($post)) {
            $post = $GLOBALS['post'];
        }
        $builderClass = apply_filters('wordland_get_property_builder', PropertyBuilder::class, $post);
        $builder = new $builderClass($post);
        if (is_a($builder, DataBuilder::class)) {
            static::$propertyBuilder = $builder;
        }
        return static::$propertyBuilder;
    }
}
