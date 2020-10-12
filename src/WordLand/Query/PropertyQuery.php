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
        }
        if (isset($rawArgs['posts_per_page'])) {
            $args['posts_per_page'] = $rawArgs['posts_per_page'];
        }
        if (isset($rawArgs['limit'])) {
            $args['posts_per_page'] = $rawArgs['limit'];
        }

        return wp_parse_args($args, array(
            'post_type' => 'property',
        ));
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
}
