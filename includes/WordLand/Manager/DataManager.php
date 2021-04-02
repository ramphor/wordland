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

        add_action('user_register', array($this, 'createAgentReferenceWithPostType'));
        add_action('profile_update', array($this, 'createAgentReferenceWithPostType'));
    }

    public function autoSetListingType($object_id, $terms, $tt_ids, $taxonomy)
    {
        // Only allow taxonomy listing type
        if (PostTypes::PROPERTY_LISTING_TYPE !== $taxonomy || ! PropertyQuery::check_wordland_data_is_exists($object_id)) {
            return;
        }
        global $wpdb;

        $firstListingType = array_shift($tt_ids);
        $listingType      = get_term($firstListingType, PostTypes::PROPERTY_LISTING_TYPE);

        if (!is_wp_error($listingType)) {
            return $wpdb->update($wpdb->prefix . 'wordland_properties', array(
                'listing_type' => intval($firstListingType),
                'listing_type_label' => $listingType->name,
            ), array(
                'property_id' => $object_id
            ));
        }
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

        if (isset($post->post_type) && in_array($post->post_type, PostTypes::get())) {
            $link = str_replace('author', wordland_get_agent_type($authordata), $link);
        } elseif (is_my_profile()) {
            $link = str_replace('author', wordland_get_agent_type(wp_get_current_user()), $link);
        } elseif (is_user_profile()) {
            $link = str_replace('author', wordland_get_agent_type(get_queried_object()), $link);
        }
        return $link;
    }

    public function checkAgentReferenceExists($userId)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->prefix}wordland_agent_references WHERE agent_id=%d LIMIT 1",
            $userId
        );

        return intval($wpdb->get_var($sql)) > 0;
    }

    public function createAgentReferenceWithPostType($userId)
    {
        if ($this->checkAgentReferenceExists($userId)) {
            return;
        }
        global $wpdb;

        $user = get_user_by('ID', $userId);

        $postId = wp_insert_post(array(
            'post_type' => PostTypes::AGENT_POST_TYPE,
            'post_title' => sprintf(__('Agent %s', 'wordland'), $user->display_name),
            'post_status' => 'publish',
            'post_author' => $userId,
            'post_content' => sprintf(__('The reference post of agent @%s', 'wordland'), $user->user_login),
        ));

        if (!is_wp_error($postId) && $postId > 0) {
            return $wpdb->insert("{$wpdb->prefix}wordland_agent_references", array(
                'agent_id' => $userId,
                'post_id' => $postId,
                'created_at' => current_time('mysql'),
            ));
        }
    }
}
