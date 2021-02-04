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

    protected static $customHooks = array();

    public function __construct($args)
    {
        $this->rawArgs = $args;
        $this->args = $this->buildArgs($args);
    }

    protected function filter_term($term, &$args)
    {
        $taxonomies = get_object_taxonomies(PostTypes::PROPERTY_POST_TYPE);
        if (!is_a($term, WP_Term::class) || !in_array($term->taxonomy, $taxonomies)) {
            return;
        }

        $taxonomy_queries = isset($args['tax_query']) ? $args['tax_query'] : array();

        $taxonomy_queries[] = array(
            'taxonomy' => $term->taxonomy,
            'terms' => $term->term_id,
            'field' => 'term_id',
            'operator' => 'IN'
        );

        $args['tax_query'] = $taxonomy_queries;
    }

    public function buildArgs($rawArgs)
    {
        $args = array();

        if (isset($rawArgs['term'])) {
            $this->filter_term($rawArgs['term'], $args);
            unset($rawArgs['term']);
        }
        if (isset($rawArgs['posts_per_page'])) {
            $args['posts_per_page'] = $rawArgs['posts_per_page'];
            unset($rawArgs['posts_per_page']);
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

    public function getWordPressQuery()
    {
        do_action_ref_array('wordland_before_get_query', array(&$this->args, $this->rawArgs));
        $wordpressQuery = new WP_Query(apply_filters(
            'wordland_property_query_args',
            $this->args
        ));
        do_action_ref_array('wordland_after_get_query', array(&$this->args, $this->rawArgs));

        $this->removeCustomHooks();

        return $wordpressQuery;
    }

    public static function get_property_metas_from_ID($property_id, $prefix = 'wlp')
    {
        global $wpdb;
        $fields = sprintf('ST_X(%1$s.location) as latitude, ST_Y(%1$s.location) as longitude', $prefix);
        $fields .= ', wlp.property_id';
        $fields .= ', wlp.price';
        $fields .= ', wlp.bedrooms';
        $fields .= ', wlp.bathrooms';
        $fields .= ', wlp.unit_price';
        $fields .= ', wlp.size';

        return $wpdb->get_row(
            $wpdb->prepare("SELECT {$fields} FROM {$wpdb->prefix}wordland_properties wlp WHERE property_id=%d LIMIT 1", $property_id)
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
        $this->logCustomHook('posts_fields', $callable, false);
    }

    public function get_sample_location_properties($property_id)
    {
        $callable = function ($where, $query) {
            if (array_element_in_array($query->post_type, PostTypes::get())) {
                $where .= ' wlp.location=(SELECT location FROM {$wpdb->prefix}wordland_properties WHERE property_id=%d)';
            }
            return $where;
        };
        add_filter('posts_where', $callable, 15, 2);
        $this->logCustomHook('posts_where', $callable, false, 15);
    }
}
