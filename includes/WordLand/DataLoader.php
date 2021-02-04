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
        add_filter('posts_join', array($this, 'joinPropertiesTable'), 10, 2);
        add_filter('posts_fields', array($this, 'chooseFields'), 10, 2);
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
        $join .= " INNER JOIN {$wpdb->prefix}wordland_properties wlp ON {$wpdb->posts}.ID=wlp.property_id";

        return $join;
    }

    public function chooseFields($fields, $query)
    {
        if (!$this->checkPostType($query->query_vars['post_type'])) {
            return $fields;
        }
        global $wpdb;

        $post_fields     = static::get_posts_fields($wpdb->posts);
        $property_fields = Property::get_meta_fields('wlp');

        return trim(sprintf('%s, %s', $post_fields, $property_fields), ', ');
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
        $builder->getPropertyVisibilities();

        do_action('wordland_dataloader_before_get_property', $builder, $post);

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

    protected static function get_posts_fields($prefix = null)
    {
        $post_fields = apply_filters('wordland_get_posts_fields', array(
            'ID',
            'post_name',
            'post_title',
            'post_type',
            'post_date',
            'post_author'
        ));
        if ($prefix) {
            $post_fields = array_map(function ($field) use ($prefix) {
                return sprintf('%s.%s', $prefix, $field);
            }, $post_fields);
        }

        return implode(', ', $post_fields);
    }

    public static function getListingTypes()
    {
        $terms = get_terms(array(
            'hide_empty' => false,
            'taxonomy' => PostTypes::PROPERTY_LISTING_TYPE
        ));
        $listingTypes = array();
        foreach ($terms as $term) {
            $listingTypes[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'taxonomy' => $term->taxonomy
            );
        }

        return apply_filters('wordland_dataloader_get_listing_types', $listingTypes);
    }
}
