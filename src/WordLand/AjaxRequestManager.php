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
        add_action('wp_ajax_wordland_get_map_markers', array($this, 'getMapMarkers'));
    }

    public function filterQueriesFromGetVariable()
    {
    }

    public function filterQueries()
    {
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

        $fields = "{$wpdb->posts}.ID, {$wpdb->posts}.post_title";
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
        $wp_query = $this->buildQuery(array_merge(
            $this->filterQueries(),
            array(
                'page' => $current_page,
                'posts_per_page' => $items_per_page,
            )
        ));
        $properties = array();
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $properties[] = $this->filterData($wp_query->post, static::$properyMappingFields);
            }
        }


        remove_filter('posts_fields', array(__CLASS__, 'filterPropertiesSelectFields'), 10, 2);
        remove_filter('posts_where', array(__CLASS__, 'postsWhere'), 10, 2);
        remove_filter('posts_join', array(__CLASS__, 'postsJoin'), 10, 2);

        wp_send_json_success(array(
            'properties' => $properties,
            'current_page' => $current_page,
            'items_per_page' => $items_per_page,
            'total_items' => 0,
            'total_page' => 0,
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
}
