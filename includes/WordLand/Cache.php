<?php
namespace WordLand;

class Cache
{
    protected static $propertyMetas;
    protected static $propertyFooterItems;
    protected static $viewedHistories = array();
    protected static $collectionPosts = array();

    public static function getPropertyMetas()
    {
        if (is_null(static::$propertyMetas)) {
            static::$propertyMetas = apply_filters('wordland_property_cache_supported_metas', array(
                'clean_price' => __('Price', 'wordland'),
                'clean_acreage' => __('Acreage', 'wordland'),
                'goto_detail' => __('Details', 'wordland'),
            ));
        }
        return static::$propertyMetas;
    }

    public static function getPropertyFooterItems()
    {
        if (is_null(static::$propertyFooterItems)) {
            static::$propertyFooterItems = apply_filters('wordland_property_cache_footer_items', array(
                'user_info' => array(
                    'type' => 'agent', // Support agent, user, develop, company, brand,
                    'default_image' => ''
                ),
                'user_actions' => array(
                    'share' => __('Share', 'wordland'),
                    'favorite' => __('Favorite', 'wordland'),
                    'compare' => __('Compare', 'wordland')
                ),
                'metas' => array(),
            ));
        }
        return static::$propertyFooterItems;
    }

    public static function checkViewed($post_id)
    {
        return isset(static::$viewedHistories[$post_id]);
    }

    public static function addViewed($post_id, $viewed = true)
    {
        static::$viewedHistories[$post_id] = $viewed;
    }

    public static function getViewed($post_id)
    {
        if (static::checkViewed($post_id)) {
            return static::$viewedHistories[$post_id];
        }
        return false;
    }
}
