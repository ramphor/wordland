<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\PostTypes;
use WordLand\Manager\PropertyBuilderManager;
use WordLand\Query\PropertyQuery;

class GlobalPost extends ModuleAbstract
{
    const MODULE_NAME = 'wordland_single_property';

    protected $mainProperty;

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('the_post', array($this, 'buildPropertyFromPost'), 10, 2);
    }

    public function buildPropertyFromPost($post, $query, $is_main_property = null)
    {
        // Only parse build global property for
        if (!in_array($post->post_type, PostTypes::get())) {
            return;
        }
        if (is_null($is_main_property)) {
            $is_main_property = $query->is_main_query() && $query->is_single();
        }

        if ($is_main_property && $this->mainProperty) {
            return $GLOBALS['property'] = $this->mainProperty;
        }

        $builder = PropertyBuilderManager::getBuilder();
        $builder->setPost($post);
        $builder->buildContent();
        $builder->build();
        $builder->getPropertyVisibilities();

        do_action('wordland_dataloader_before_get_property', $builder, $post);

        $property = $builder->getProperty($is_main_property ? 'single' : 'global');


        return $GLOBALS['property'] = apply_filters(
            'wordland_build_property_data',
            $property,
            $builder
        );
    }

    public function setMainProperty(&$property)
    {
        if ($this->mainProperty) {
            return null;
        }
        $this->mainProperty = $property;
    }

    public function loadMainPropertyFromGlobalPost()
    {

        if ($this->mainProperty) {
            return $this->mainProperty;
        }
        global $post, $wp_query;


        if ($wp_query->is_main_query() && $wp_query->is_single()) {
            $propertyMetas = PropertyQuery::get_property_metas_from_ID($post->ID);
            foreach($propertyMetas as $key => $value) {
                $post->$key = $value;
            }
            unset($propertyMetas, $key, $value);

            $property = $this->buildPropertyFromPost($post, $wp_query, true);

            $this->setMainProperty($property);

            return $this->mainProperty;
        }

        return false;
    }
}
