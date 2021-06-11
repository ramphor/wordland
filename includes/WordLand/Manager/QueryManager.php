<?php
namespace WordLand\Manager;

use WordLand\Abstracts\ManagerAbstract;
use WordLand\PostTypes;
use WordLand\Property;
use WordLand\Query\PropertyQuery;

class QueryManager extends ManagerAbstract
{
    protected static $instance;

    public function __construct()
    {
        add_action('after_setup_theme', array($this, 'customQueries'));
    }

    public function customQueries()
    {
        add_action('wordland_init_property_query', array($this, 'registerCustomQueries'));
        add_action('wordland_end_property_query', array($this, 'removeCustomQueries'));

        add_filter('wordland_setup_filter_markers_mapping_fields', array($this, 'add_same_location_fields'));

        add_action('wordland_agent_query_init', array($this, 'customUserQueries'), 10, 2);
    }

    public function registerCustomQueries($scope)
    {
        add_filter('posts_join', array($this, 'joinTables'), 15, 2);
        add_filter('posts_fields', array($this, 'selectWordLandFields'), 15, 2);
        if ($scope === 'listing') {
            add_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);
            add_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }

        // Caching database query
        add_filter('posts_pre_query', array($this, 'loadOrInitCachePropertiesQuery'), 15, 2);
        add_filter('posts_results', array($this, 'createCacheProperties'), 15, 2);
    }

    public function selectWordLandFields($fields, $query)
    {
        global $wpdb;
        return sprintf(
            '%s, %s',
            $fields,
            Property::get_meta_fields($wpdb->prefix . 'wordland_properties')
        );
    }

    public function removeCustomQueries($scope)
    {
        remove_filter('posts_join', array($this, 'joinTables'), 15, 2);
        remove_filter('posts_fields', array($this, 'selectWordLandFields'), 15, 2);

        if ($scope === 'listing') {
            remove_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);
            remove_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }

        // Caching database query
        remove_filter('posts_pre_query', array($this, 'loadOrInitCachePropertiesQuery'), 15);
        remove_filter('posts_results', array($this, 'createCacheProperties'), 15);
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

    public function joinTables($join, $query)
    {
        if (!$this->checkPostType($query->query_vars['post_type'])) {
            return $join;
        }
        global $wpdb;

        $join .= " INNER JOIN {$wpdb->prefix}wordland_properties ON {$wpdb->posts}.ID = {$wpdb->prefix}wordland_properties.property_id";

        if (count($query->query_vars['tax_query']) > 0) {
            if (strpos($join, "LEFT JOIN {$wpdb->term_relationships}") !== false) {
                $rule =  " INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id";
            } else {
                $rule  =  " INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_relationships}.object_id = wp_posts.ID";
                $rule .= " INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id";
            }

            $join = empty($join) ? $rule : $join . $rule;
        }
        return $join;
    }

    public function groupByPropertyLocation($groupby, $query)
    {
        global $wpdb;

        if ($groupby != '') {
            $groupby .= ", {$wpdb->prefix}wordland_properties.coordinate";
        } else {
            $groupby = "{$wpdb->prefix}wordland_properties.coordinate";
        }
        if (strpos($groupby, 'wp_posts.ID,') !== false) {
            $groupby = preg_replace('/wp_posts\.ID\,\n?/', '', $groupby);
        }

        if (count($query->query_vars['tax_query'])) {
            $groupby .= ", {$wpdb->term_taxonomy}.taxonomy";
        }

        return $groupby;
    }

    public function selectCountPropertySameLocation($fields, $query)
    {
        if (!$this->checkPostType($query->query_vars['post_type'])) {
            return $fields;
        }
        global $wpdb;

        $post_fields     = PropertyQuery::get_posts_fields($wpdb->posts);
        $property_fields = Property::get_meta_fields(sprintf('%swordland_properties', $wpdb->prefix));

        $fields  =  trim(sprintf('%s, %s', $post_fields, $property_fields), ', ');
        $fields .= ", COUNT({$wpdb->posts}.ID) as same_location_items";

        return $fields;
    }

    public function add_same_location_fields($fields)
    {
        $fields['same_location_items'] = array(
            'source' => 'same_location_items',
            'type' => 'int'
        );
        return $fields;
    }

    public static function getListingTypes()
    {
        $terms = get_terms(array(
            'hide_empty' => false,
            'taxonomy' => PostTypes::PROPERTY_LISTING_TYPE
        ));
        $listingTypes = array();
        foreach ($terms as $term) {
            $listingTypes[$term->term_id] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'taxonomy' => $term->taxonomy
            );
        }

        return apply_filters('wordland_dataloader_get_listing_types', $listingTypes);
    }

    public function customUserQueries($args, $agentQuery)
    {
        $joinTableCallback = function ($pre, $query) {
            global $wpdb;

            $query->query_from = sprintf(
                '%s %s',
                $query->query_from,
                $wpdb->_real_escape("INNER JOIN {$wpdb->prefix}wordland_agents ON {$wpdb->prefix}wordland_agents.user_id={$wpdb->users}.ID")
            );

            return $pre;
        };

        add_filter('users_pre_query', $joinTableCallback, 10, 2);
        $agentQuery->createCustomFilterLog($joinTableCallback, 10);
    }

    protected function getCacheTimes()
    {
        if (defined('WORDLAND_PROPERTIES_CACHE_TIMES')) {
            return intval(constant('WORDLAND_PROPERTIES_CACHE_TIMES'));
        }
        return apply_filters('wordland_property_cache_times', 1 * 24 * 60 * 60);
    }

    public function loadOrInitCachePropertiesQuery($posts, $query)
    {
        $query->wordland_cache_key = sprintf('wl_cached_properties_%s', md5($query->request));
        $cached_properties = get_transient($query->wordland_cache_key);
        if ($cached_properties) {
            $query->load_from_transient = true;
            $query->found_posts = intval(get_transient($query->wordland_cache_key . '_found_posts'));

            return $cached_properties;
        }
        return $posts;
    }

    public function createCacheProperties($posts, $query)
    {
        if (!isset($query->load_from_transient) && isset($query->wordland_cache_key)) {
            set_transient(
                $query->wordland_cache_key,
                $posts,
                $this->getCacheTimes()
            );

            // Caching found_posts
            set_transient(
                $query->wordland_cache_key . '_found_posts',
                $query->found_posts,
                $this->getCacheTimes()
            );
        }
        return $posts;
    }
}
