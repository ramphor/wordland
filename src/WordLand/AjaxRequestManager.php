<?php
namespace WordLand;

use WordLand\Query\PropertyQuery;

class AjaxRequestManager
{
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_wordland_filter_properties', array($this, 'filterProperties'));
        add_action('wp_ajax_wordland_get_map_markers', array($this, 'getMapMarkers'));
    }

    public function filterQueriesFromGetVariable()
    {
    }

    public function filterQueries()
    {
    }

    protected function buildQuery()
    {
        $query = new PropertyQuery($this->filterQueries());
        return $query->getWordPressQuery();
    }

    public function filterMarkersSelectFields($fields, &$query)
    {
        return $fields;
    }

    public function filterPropertiesSelectFields($fields, &$query)
    {
        $fields;
    }

    public function postsWhere($clauses, &$query)
    {
        return $clauses;
    }

    /**
     * Main methods to get data
     */
    public function filterProperties()
    {
        add_filter('posts_clauses', array($this, 'postsWhere'), 10, 2);

        add_filter('posts_fields', array($this, 'filterPropertiesSelectFields'), 10, 2);
        $wp_query = $this->buildQuery();
        remove_filter('posts_fields', array($this, 'filterPropertiesSelectFields'), 10, 2);

        wp_send_json_success(array());
    }

    public function getMapMarkers()
    {
        add_filter('posts_clauses', array($this, 'postsWhere'), 10, 2);

        add_filter('posts_fields', array($this, 'filterMarkersSelectFields'), 10, 2);
        $wp_query = $this->buildQuery();
        remove_filter('posts_fields', array($this, 'filterMarkersSelectFields'), 10, 2);

        wp_send_json_success(array());
    }
}
