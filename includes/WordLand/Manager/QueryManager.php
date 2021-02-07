<?php
namespace WordLand\Manager;

use WordLand\Abstracts\ManagerAbstract;

class QueryManager extends ManagerAbstract
{
    public function __construct()
    {
        add_action('after_setup_theme', array($this, 'customQueries'));
    }

    public function customQueries()
    {
        add_action('wordland_init_property_query', array($this, 'registerCustomQueries'));
        add_action('wordland_end_property_query', array($this, 'removeCustomQueries'));

        add_filter('wordland_setup_filter_markers_mapping_fields', array($this, 'add_same_location_fields'));
    }

    public function registerCustomQueries($scope)
    {
        add_filter('posts_join', array($this, 'joinTables'), 15, 2);

        if ($scope === 'listing') {
            add_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);
            add_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }
    }

    public function removeCustomQueries($scope)
    {
        remove_filter('posts_join', array($this, 'joinTables'), 15, 2);

        if ($scope === 'listing') {
            remove_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);
            remove_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }
    }

    public function joinTables($join, $query)
    {
        global $wpdb;

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
            $groupby .= ", {$wpdb->prefix}wordland_properties.location";
        } else {
            $groupby = "{$wpdb->prefix}wordland_properties.location";
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
        global $wpdb;
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
}
