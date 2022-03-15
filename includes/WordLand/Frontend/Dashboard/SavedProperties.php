<?php

namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;
use WP_Query;

class SavedProperties extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved-properties';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        $savedPropertiesPage = wordland_get_option('saved_properties_page');
        return array(
            'label' => __('Favorite properties', 'wordland'),
            'url' => $savedPropertiesPage ? get_permalink($savedPropertiesPage) : '#',
        );
    }

    public function filterSavedProperties($where, $query) {
        global $wpdb;

        $where .= $wpdb->prepare(" AND (rcg.user_id=%d AND rcg.collection=%s)", get_current_user_id(), 'favorite_property');

        return $where;
    }

    public function sortSavedProperties($orderby, $query) {
        global $wpdb;

        $orderby = 'rcg.added_at DESC,' . $orderby;

        return $orderby;
    }

    public function joinCollectionGlobalTable($join, $query) {
        global $wpdb;
        if (!empty($join)) {
            $join .= " ";
        }
        $join .= "INNER JOIN {$wpdb->prefix}ramphor_collection_global rcg ON {$wpdb->posts}.ID=rcg.post_id";

        return $join;
    }


    /**
     * @return WP_Query
     */
    public function getSavedProperties()
    {
        $args = array(
            'post_type' => 'property',
            'posts_per_page' => 10, // 10
            'paged' => get_query_var('paged', 1),
        );

        // Join với bảng `ramphor_collection_global` để get các favorited properties
        add_filter('posts_join', array($this, 'joinCollectionGlobalTable'), 10, 2);

        // Chỗ này custom lại điều kiện get_posts
        add_filter('posts_where', array($this, 'filterSavedProperties'), 10, 2);

        // Custom sort lại kết quả của query
        add_filter('posts_orderby', array($this, 'sortSavedProperties'), 10, 2);


        // Get posts như flow bình thường của WordPress
        $savedProperties = new WP_Query($args);


        // Gỡ bỏ phần custom ra để tránh ảnh hưởng đến xử lý ở chỗ khác
        remove_filter('posts_orderby', array($this, 'sortSavedProperties'), 10, 2);
        remove_filter('posts_where', array($this, 'filterSavedProperties'), 10, 2);
        remove_filter('posts_join', array($this, 'joinCollectionGlobalTable'), 10, 2);

        return $savedProperties;
    }

    public function render()
    {
        $savedProperties = $this->getSavedProperties();

        $postLayout = Template::createPostLayout('card', $savedProperties);
        $postLayout->setOptions(array(
            'columns' => 4,
            'show_paginate' => true,
        ));

        return $postLayout->render(false);
    }
}
