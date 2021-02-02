<?php
namespace WordLand\Query;

use WP_Query;
use WP_Term;
use WordLand\PostTypes;
use WordLand\Abstracts\BaseQuery;

class PropertyQuery extends BaseQuery
{
    protected $wordpressQuery;
    protected $rawArgs;
    protected $args;

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

    public function getWordPressQuery()
    {
        do_action_ref_array('wordland_before_get_query', array(&$this->args, $this->rawArgs));
        $wordpressQuery = new WP_Query(apply_filters(
            'wordland_property_query_args',
            $this->args
        ));
        do_action_ref_array('wordland_after_get_query', array(&$this->args, $this->rawArgs));

        return $wordpressQuery;
    }

    public static function get_property_metas_from_ID($property_id)
    {
        global $wpdb;
        $fields .= 'ST_X(w.location) as latitude, ST_Y(w.location) as longitude';
        $fields .= ', w.property_id';
        $fields .= ', w.price';
        $fields .= ', w.bedrooms';
        $fields .= ', w.bathrooms';
        $fields .= ', w.unit_price';
        $fields .= ', w.size';

        return $wpdb->get_row(
            $wpdb->prepare("SELECT {$fields} FROM {$wpdb->prefix}wordland_properties w WHERE property_id=%d LIMIT 1", $property_id)
        );
    }
}
