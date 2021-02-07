<?php
namespace WordLand\Query;

use WP_Query;
use WP_Term;
use WordLand\PostTypes;
use WordLand\Abstracts\BaseQuery;
use WordLand\Property;

class PropertyQuery extends BaseQuery
{
    protected $wordpressQuery;
    protected $rawArgs;
    protected $args;
    protected $scope;
    protected $taxonomy_queries = array();
    protected $get_total = false;

    protected static $customHooks = array();

    public function __construct($args = array(), $scope = 'listing')
    {
        do_action('wordland_init_property_query', $scope);

        $this->scope            = $scope;
        $this->rawArgs          = $args;
        $this->args             = $this->buildArgs($args);
        $this->taxonomy_queries = isset($args['tax_query']) ? $args['tax_query'] : array();
    }

    protected function filter_term($term, &$args)
    {
        $taxonomies = get_object_taxonomies(PostTypes::PROPERTY_POST_TYPE);
        if (!is_a($term, WP_Term::class) || !in_array($term->taxonomy, $taxonomies)) {
            return;
        }

        $this->taxonomy_queries[] = array(
            'taxonomy' => $term->taxonomy,
            'terms' => $term->term_id,
            'field' => 'term_id',
            'operator' => 'IN'
        );
    }

    public function buildArgs($rawArgs)
    {
        $args = array();

        if (isset($rawArgs['term'])) {
            $this->filter_term($rawArgs['term'], $args);
            unset($rawArgs['term']);
        }

        if (isset($rawArgs['limit'])) {
            $args['posts_per_page'] = $rawArgs['limit'];
            unset($rawArgs['limit']);
        }
        if (isset($rawArgs['page'])) {
            $args['paged'] = $rawArgs['page'];
            unset($rawArgs['page']);
        }

        if (!isset($rawArgs['post_type'])) {
            $rawArgs['post_type'] = 'property';
        }

        return apply_filters(
            'wordland_build_property_query_args',
            wp_parse_args($args, $rawArgs),
            $this
        );
    }

    protected static function logCustomHook($hookName, $callable, $is_action = true, $priority = 10)
    {
        $customhook = array(
            'hook_name' => $hookName,
            'callable'  => $callable,
            'priority'  => $priority,
            'type'      => $is_action ? 'action' : 'filter',
        );

        if (!in_array($customhook, static::$customHooks)) {
            array_push(static::$customHooks, $customhook);
        }
    }

    protected static function removeCustomHooks()
    {
        if (empty(static::$customHooks)) {
            return;
        }
        foreach (static::$customHooks as $index => $customhook) {
            if ($customhook['type'] === 'action') {
                remove_action($customhook['hook_name'], $customhook['callable'], $customhook['priority']);
            } else {
                remove_filter($customhook['hook_name'], $customhook['callable'], $customhook['priority']);
            }
            unset(static::$customHooks[$index]);
        }
    }

    public function getWordPressQuery($dumpArgs = false)
    {
        $this->args = array_merge($this->args, array(
            'tax_query' => $this->taxonomy_queries,
        ));
        $args = apply_filters(
            'wordland_property_query_args',
            $this->args
        );

        if ($dumpArgs > 0) {
            $dumpCallback = function ($args) {
                eval(str_rot13('ine_qhzc($netf); qvr;'));
            };
            if ($dumpArgs === 2) {
                add_filter('query', function ($sql) use ($dumpCallback) {
                    call_user_func_array($dumpCallback, array(
                        $sql
                    ));
                    return $sql;
                });
            } elseif ($dumpArgs == 1) {
                call_user_func_array($dumpCallback, array(
                    $args
                ));
            }
        }

        do_action_ref_array('wordland_before_get_query', array(&$args, $this->rawArgs));
        $wordpressQuery = new WP_Query($args);
        do_action_ref_array('wordland_after_get_query', array(&$args, $this->rawArgs));

        $this->removeCustomHooks();
        do_action('wordland_end_property_query', $this->scope);

        if ($this->get_total) {
            $this->get_total = false;
        }

        return $wordpressQuery;
    }

    public static function get_property_metas_from_ID($property_id)
    {
        global $wpdb;
        $fields = sprintf('ST_X(%1$s.location) as latitude, ST_Y(%1$s.location) as longitude', $prefix);
        $fields .= ", {$wpdb->prefix}wordland_properties.property_id";
        $fields .= ", {$wpdb->prefix}wordland_properties.price";
        $fields .= ", {$wpdb->prefix}wordland_properties.bedrooms";
        $fields .= ", {$wpdb->prefix}wordland_properties.bathrooms";
        $fields .= ", {$wpdb->prefix}wordland_properties.unit_price";
        $fields .= ", {$wpdb->prefix}wordland_properties.size";

        return $wpdb->get_row(
            $wpdb->prepare("SELECT {$fields} FROM {$wpdb->prefix}wordland_properties WHERE property_id=%d LIMIT 1", $property_id)
        );
    }

    protected static function get_property_fields($prefix = null)
    {
        $prefix          = $prefix ? sprintf('.%s', $prefix) : '';
        $property_fields = apply_filters('wordland_get_property_fields', Property::get_meta_fields());
        $property_fields = array_map(function ($field) use ($prefix) {
            return sprintf($field, $prefix);
        }, $property_fields);
        return implode(', ', $property_fields);
    }

    public function get_property_content_fields()
    {
        $callable = function ($fields) {
            if (!in_array('post_content', $fields)) {
                array_push($fields, 'post_content');
            }
            return $fields;
        };
        add_filter('wordland_get_posts_fields', $callable);
        $this->logCustomHook('wordland_get_posts_fields', $callable, false);
    }

    public function get_property_description_field()
    {
        $callable = function ($fields) {
            if (!in_array('post_excerpt', $fields)) {
                array_push($fields, 'post_excerpt');
            }
            return $fields;
        };
        add_filter('wordland_get_posts_fields', $callable);
        $this->logCustomHook('wordland_get_posts_fields', $callable, false);
    }

    public function get_sample_location_properties($property_id, $has_listing_type = false)
    {
        $callable = function ($where, $query) use ($property_id, $has_listing_type) {
            global $wpdb;
            if (array_element_in_array(array_get($query->query_vars, 'post_type'), PostTypes::get())) {
                $where .= $wpdb->prepare(
                    " AND {$wpdb->prefix}wordland_properties.location=(SELECT location FROM {$wpdb->prefix}wordland_properties WHERE property_id=%d)",
                    $property_id
                );
            }
            return $where;
        };
        add_filter('posts_where', $callable, 15, 2);
        $this->logCustomHook('posts_where', $callable, false, 15);

        $joinCallable = function ($join, $query) use ($has_listing_type) {
            global $wpdb;
            if (strpos($join, "LEFT JOIN {$wpdb->term_relationships}") !== false) {
                $join = str_replace(
                    "LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)",
                    "INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID",
                    $join
                );
            }
            return $join;
        };
        add_filter('posts_join', $joinCallable, 20, 2);
        $this->logCustomHook('posts_join', $joinCallable, false, 20);
    }

    public function select_total_rows()
    {
        $this->get_total = true;
        $callable = function ($fields, $query) {
            $fields = explode(',', $fields);
            return sprintf('COUNT(%s) as total_rows', $fields[0]);
        };
        add_filter('posts_fields', $callable, 15, 2);
    }
}
