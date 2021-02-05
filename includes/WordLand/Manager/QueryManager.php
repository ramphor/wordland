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
        add_filter('posts_where', array($this, 'whereConditions'), 15, 2);
        add_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);

        if ($scope === 'listing') {
            add_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }
    }

    public function removeCustomQueries($scope)
    {
        remove_filter('posts_join', array($this, 'joinTables'), 15, 2);
        remove_filter('posts_where', array($this, 'whereConditions'), 15, 2);
        remove_filter('posts_fields', array($this, 'selectCountPropertySameLocation'), 15, 2);

        if ($scope === 'listing') {
            remove_filter('posts_groupby', array($this, 'groupByPropertyLocation'), 10, 2);
        }
    }

    public function joinTables($join, $query)
    {
        global $wpdb;
        $rule =  " INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_relationships}.object_id = wp_posts.ID
INNER JOIN wp_term_taxonomy tt ON {$wpdb->term_relationships}.term_taxonomy_id = tt.term_taxonomy_id";
        // $rule.= ' INNER JOIN wp_terms t ON t.term_id = tt.term_id';

        $join = empty($join) ? $rule : $join . $rule;

        if (strpos($join, 'LEFT JOIN wp_term_relationships') !== 0) {
            $join = str_replace('LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id)', '', $join);
        }

        return $join;
    }

    public function whereConditions($where, $query)
    {
        global $wpdb;
        $where .= $wpdb->prepare(" AND tt.taxonomy=%s", 'listing_type');
        return $where;
    }

    public function groupByPropertyLocation($groupby, $query, $prefix = 'wlp')
    {
        if ($groupby != '') {
            $groupby .= ", {$prefix}.location";
        } else {
            $groupby = "{$prefix}.location";
        }
        if (strpos($groupby, 'wp_posts.ID,') !== false) {
            $groupby = preg_replace('/wp_posts\.ID\,\n?/', '', $groupby);
        }
        $groupby .= ', tt.taxonomy';
        return $groupby;
    }

    public function selectCountPropertySameLocation($fields, $query)
    {
        global $wpdb;
        $fields .= ", COUNT({$wpdb->posts}.ID) as same_location_items";
        // $fields .= ', t.slug as listing_type';

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
