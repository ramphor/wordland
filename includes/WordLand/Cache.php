<?php
namespace WordLand;

use WordLand\PostTypes;

class Cache
{
    protected static $propertyMetas;
    protected static $propertyFooterItems;
    protected static $collectionPosts = array();
    protected static $visitedProperties = null;

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
        if (is_null(static::$visitedProperties)) {
            static::getVisitedProperties();
        }
        static::$visitedProperties[$post_id] = $viewed;
    }

    public static function getVisitedProperties()
    {
        global $wpdb;
        if (is_user_logged_in()) {
            $sql = $wpdb->prepare(
                "SELECT {$wpdb->prefix}ramphor_post_views.post_id
                FROM {$wpdb->prefix}ramphor_post_views
                    INNER JOIN {$wpdb->posts} ON {$wpdb->prefix}ramphor_post_views.post_id = {$wpdb->posts}.ID
                WHERE {$wpdb->posts}.post_type IN (%s)
                    AND {$wpdb->prefix}ramphor_post_views.user_id=%d",
                implode(', ', PostTypes::get()),
                get_current_user_id()
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT {$wpdb->prefix}ramphor_view_histories.post_id
                FROM {$wpdb->prefix}ramphor_view_histories
                    INNER JOIN {$wpdb->posts} ON {$wpdb->prefix}ramphor_view_histories.post_id = {$wpdb->posts}.ID
                WHERE {$wpdb->posts}.post_type IN (%s)
                    AND {$wpdb->prefix}ramphor_view_histories.user_id=%d
                    AND {$wpdb->prefix}ramphor_view_histories.client_ip=%s
                    AND {$wpdb->prefix}ramphor_view_histories.last_views >= DATE(NOW()) - INTERVAL 7 DAY",
                implode(', ', PostTypes::get()),
                0,
                wordland_get_real_ip_address(),
                get_option('wordland_guest_history_tracking_time', 7)
            );
        }

        $rows = $wpdb->get_results($sql, OBJECT_K);
        if ($rows) {
            return static::$visitedProperties = $rows;
        }

        return static::$visitedProperties = array();
    }
}
