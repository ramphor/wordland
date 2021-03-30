<?php
namespace WordLand\Frontend\Dashboard;

use Ramphor\User\Abstracts\MyProfileAbstract;
use WordLand\Template;

class SavedSearches extends MyProfileAbstract
{
    const FEATURE_NAME = 'saved-searches';

    protected $priority = 15;

    public function getName()
    {
        return static::FEATURE_NAME;
    }

    public function getMenuItem()
    {
        $savedSearchesPage = wordland_get_option('saved_searches_page');
        return array(
            'label' => __('Saved searches', 'wordland'),
            'url' => $savedSearchesPage ? get_permalink($savedSearchesPage) : '#',
        );
    }

    public static function get_saved_searches()
    {
        global $wpdb;
        $searches_per_page = 10;
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
        $offset = 0;
        if ($current_page > 1) {
            $offset = $searches_per_page * ($current_page - 1);
        }

        if (is_user_logged_in()) {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wordland_saved_searches WHERE user_id=%d ORDER BY created_at DESC LIMIT %d OFFSET %d",
                get_current_user_id(),
                $searches_per_page,
                $offset
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wordland_saved_searches WHERE user_id=%d AND guest_ip=%s ORDER BY created_at DESC LIMIT %d OFFSET %d",
                0,
                wordland_get_real_ip_address(),
                $searches_per_page,
                $offset
            );
        }

        $searches = $wpdb->get_results($sql);
        if (empty($searches)) {
            return array();
        }
        return $searches;
    }

    public function render()
    {
        $saved_searches = static::get_saved_searches();

        return Template::render(
            'agent/my-profile/features/saved-searches',
            array(
                'saved_searches' => $saved_searches,
            ),
            null,
            false
        );
    }
}
