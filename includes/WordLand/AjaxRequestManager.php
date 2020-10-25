<?php
namespace WordLand;

use WordLand\Query\PropertyQuery;

class AjaxRequestManager
{
    protected static $instance;
    protected static $defaultMappingFields = array(
        'ID' => array(
            'source' => 'ID',
            'type' => 'int',
            'default' => 0
        ),
        'name' => 'post_title',
        'lat' =>    array(
            'source' => 'latitude',
            'type' => 'float',
        ),
        'lng' => array(
            'source' => 'longitude',
            'type' => 'float',
        ),
        'price' => array(
            'source' => 'price',
            'type' => 'decimal',
            'default' => 0
        ),
        'beds' => array(
            'source' => 'ID',
            'type' => 'int',
            'default' => 0
        ),
        'baths' => array(
            'source' => 'bathrooms',
            'type' => 'int',
            'default' => 0
        ),
        'unit_price' => array(
            'source' => 'price',
            'type' => 'decimal',
            'default' => 0
        ),
        'size' => array(
            'source' => 'bathrooms',
            'type' => 'float',
            'default' => 0
        )
    );
    protected static $markerMappingFields;
    protected static $properyMappingFields;

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
        add_action('wp_ajax_nopriv_wordland_filter_properties', array($this, 'filterProperties'));
        add_action('wp_ajax_wordland_get_map_markers', array($this, 'getMapMarkers'));
        add_action('wp_ajax_nopriv_wordland_get_map_markers', array($this, 'getMapMarkers'));
        add_action('wp_ajax_wordland_get_map_boundaries', array($this, 'getMapBoundaries'));
        add_action('wp_ajax_nopriv_wordland_get_map_boundaries', array($this, 'getMapBoundaries'));
        add_action('wp_ajax_wordland_get_property', array($this, 'getProperty'));
        add_action('wp_ajax_nopriv_wordland_get_property', array($this, 'getProperty'));
    }

    public function filterQueriesFromGetVariable()
    {
    }

    public function filterQueries($systemArgs = array())
    {
        // Parse from user data, actions
        $parsedArgs = array();

        return wp_parse_args($systemArgs, $parsedArgs);
    }

    protected function buildQuery($args = array())
    {
        $query = new PropertyQuery($args);
        return $query->getWordPressQuery();
    }

    public static function filterMarkersSelectFields($fields, $query)
    {
        global $wpdb;

        $fields = "{$wpdb->posts}.ID, {$wpdb->posts}.post_title";
        $fields .= ', ST_X(w.location) as latitude, ST_Y(w.location) as longitude';
        $fields .= ', w.property_id';
        $fields .= ', w.price';
        $fields .= ', w.bedrooms';
        $fields .= ', w.bathrooms';
        $fields .= ', w.unit_price';
        $fields .= ', w.size';

        return apply_filters('wordland_filter_markers_fields', $fields);
    }

    public static function filterPropertiesSelectFields($fields, $query)
    {
        global $wpdb;

        $fields = "{$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->posts}.post_name, {$wpdb->posts}.post_date, {$wpdb->posts}.post_type";
        $fields .= ', ST_X(w.location) as latitude, ST_Y(w.location) as longitude';
        $fields .= ', w.property_id';
        $fields .= ', w.price';
        $fields .= ', w.bedrooms';
        $fields .= ', w.bathrooms';
        $fields .= ', w.unit_price';
        $fields .= ', w.size';

        return apply_filters('wordland_filter_properties_fields', $fields);
    }

    public static function postsJoin($join, $query)
    {
        global $wpdb;
        $join .= sprintf(
            "RIGHT JOIN %swordland_properties w ON {$wpdb->posts}.ID=w.property_ID",
            $wpdb->prefix
        );

        return $join;
    }

    public static function postsWhere($where, $query)
    {
        return $where;
    }

    protected function parseArrayField($data, $field)
    {
        $value = null;
        $field = wp_parse_args($field, array(
            'default' => null,
        ));
        if (isset($field['source'])) {
            $dataKey = $field['source'];
            $value = isset($data->$dataKey) ? $data->$dataKey : $field['default'];
            if (isset($field['type'])) {
                $value = $this->parseData($value, $field['type']);
            }
        }

        return $value;
    }

    /** Processing data */
    protected function filterData($data, $mappingFields)
    {
        $convertedData = array();
        foreach ($mappingFields as $key => $field) {
            if (is_string($field)) {
                if (isset($data->$field)) {
                    $convertedData[$key] = $data->$field;
                } else {
                    $convertedData[$key] = null;
                }
            } elseif (is_array($field)) {
                $convertedData[$key] = $this->parseArrayField($data, $field);
            }
        }
        return $convertedData;
    }

    protected function parseData($value, $type)
    {
        switch ($type) {
            case 'int':
                return intval($value);
            case 'decimal':
            case 'float':
            case 'double':
                return floatval($value);
            case 'boolean':
            case 'bool':
                return boolval($value);
            default:
                return $value;
        }
    }


    /**
     * Main methods to get data
     */
    public function filterProperties()
    {
        if (is_null(static::$markerMappingFields)) {
            static::$properyMappingFields = apply_filters(
                'wordland_setup_filter_properties_mapping_fields',
                static::$defaultMappingFields
            );
        }

        add_filter('posts_join', array(__CLASS__, 'postsJoin'), 10, 2);
        add_filter('posts_where', array(__CLASS__, 'postsWhere'), 10, 2);
        add_filter('posts_fields', array(__CLASS__, 'filterPropertiesSelectFields'), 10, 2);

        $current_page = 1;
        $items_per_page = 40;
        $wp_query = $this->buildQuery($this->filterQueries(array(
            'page' => $current_page,
            'posts_per_page' => 2,
        )));
        $properties = array();
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $post = $wp_query->post;
                $currentIndex = $wp_query->current_post;
                $properties[$currentIndex] = $this->filterData($post, static::$properyMappingFields);
                $properties[$currentIndex]['thumbnail_url'] = wp_get_attachment_image_url(
                    get_post_thumbnail_id($post->ID),
                    'medium'
                );
                $properties[$currentIndex]['url'] = get_permalink($wp_query->post);
            }
        }

        remove_filter('posts_fields', array(__CLASS__, 'filterPropertiesSelectFields'), 10, 2);
        remove_filter('posts_where', array(__CLASS__, 'postsWhere'), 10, 2);
        remove_filter('posts_join', array(__CLASS__, 'postsJoin'), 10, 2);

        $total_items = $wp_query->found_posts;
        $items_per_page = array_get($wp_query->query_vars, 'posts_per_page', 5);
        $current_page = array_get($wp_query->query_vars, 'paged', 1);
        $current_page = $current_page > 0 ? $current_page : 1;

        wp_send_json_success(array(
            'properties' => $properties,
            'current_page' => $current_page,
            'items_per_page' => $items_per_page,
            'total_items' => $total_items,
            'found_items' => $wp_query->post_count,
            'total_page' => ceil($total_items/$items_per_page),
        ));
    }

    public function getMapMarkers()
    {
        if (is_null(static::$markerMappingFields)) {
            static::$markerMappingFields = apply_filters(
                'wordland_setup_filter_markers_mapping_fields',
                static::$defaultMappingFields
            );
        }

        add_filter('posts_join', array(__CLASS__, 'postsJoin'), 10, 2);
        add_filter('posts_where', array(__CLASS__, 'postsWhere'), 10, 2);

        add_filter('posts_fields', array(__CLASS__, 'filterMarkersSelectFields'), 10, 2);
        $wp_query = $this->buildQuery($this->filterQueries());
        $markers = array();
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $markers[] = $this->filterData($wp_query->post, static::$markerMappingFields);
            }
        }

        remove_filter('posts_fields', array(__CLASS__, 'filterMarkersSelectFields'), 10, 2);

        remove_filter('posts_where', array(__CLASS__, 'postsWhere'), 10, 2);
        remove_filter('posts_join', array(__CLASS__, 'postsJoin'), 10, 2);
        wp_send_json_success($markers);
    }

    public function getProperty()
    {
        if (!isset($_REQUEST['property_id'])) {
            return wp_send_json_error(__('The property ID is invalid to get data', 'wordland'));
        }
    }
}
