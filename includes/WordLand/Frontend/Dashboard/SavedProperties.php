<?php

namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

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

    public static function get_saved_properties()
    {
        global $wpdb;
        $searches_per_page = 10;
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
        $offset = 0;
        if ($current_page > 1) {
            $offset = $searches_per_page * ($current_page - 1);
        }

        $sql = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}ramphor_collection_global g WHERE g.user_id=%d AND collection=%s  ORDER BY added_at DESC LIMIT %d OFFSET %d",
            get_current_user_id(),
            "favorite_property",
            $searches_per_page,
            $offset
        );

        $properties = $wpdb->get_results($sql);
        if (empty($properties)) {
            return array();
        }
        return $properties;
    }

    public function render()
    {
        $saved_properties = static::get_saved_properties();
        return Template::render(
            'agent/my-profile/features/saved-properties',
            array(
                'saved_properties' => $saved_properties,
            ),
            null,
            false
        );
    }
}
