<?php
namespace WordLand\Manager;

use WordLand\Abstracts\ManagerAbstract;
use WordLand\PostTypes;
use WordLand\Query\PropertyQuery;

class DataManager extends ManagerAbstract
{
    protected static $instance;

    protected function __construct()
    {
        add_action('init', array($this, 'manage'));
    }

    public function manage()
    {
        add_action('set_object_terms', array($this, 'autoSetListingType'), 10, 4);
        add_filter('author_link', array($this, 'changeAgentAuthorLink'), 10, 2);
        foreach ((array)PostTypes::get() as $post_type) {
            add_action("publish_{$post_type}", array($this, 'changeUpdatedTime'), 10, 2);
        }
    }

    public function autoSetListingType($object_id, $terms, $tt_ids, $taxonomy)
    {
        // Only allow taxonomy listing type
        if (PostTypes::PROPERTY_LISTING_TYPE !== $taxonomy && !PropertyQuery::check_wordland_data_is_exists($object_id)) {
            return;
        }
        global $wpdb;

        $firstListingType = array_shift($tt_ids);

        return $wpdb->update($wpdb->prefix . 'wordland_properties', array(
            'listing_type' => intval($firstListingType)
        ), array(
            'property_id' => $object_id
        ));
    }

    public function changeUpdatedTime($propertyId, $originalPost)
    {
        global $wpdb;

        return $wpdb->update($wpdb->prefix . 'wordland_properties', array(
            'updated_at' => current_time('mysql')
        ), array(
            'property_id' => $propertyId
        ));
    }

    public function changeAgentAuthorLink($link, $author_id)
    {
        global $authordata, $post;

        if (in_array($post->post_type, PostTypes::get())) {
            $link = str_replace('author', wordland_get_agent_type($authordata), $link);
        } elseif (is_my_profile()) {
            $link = str_replace('author', wordland_get_agent_type(wp_get_current_user()), $link);
        }
        return $link;
    }
}
