<?php
namespace WordLand\Query;

use WordLand\Abstracts\BaseQuery;

class PropertyQuery extends BaseQuery
{
    protected $wordpressQuery;
    protected $args;

    public function __construct($args)
    {
        $this->args = $this->buildArgs($args);
    }

    public function buildArgs($rawArgs)
    {
        return wp_parse_args($rawArgs, array(
            'post_type' => 'property',
        ));
    }

    public function getWordPressQuery()
    {
        $wordpressQuery = new WP_Query($this->args);
        return apply_filters(
            'wordland_get_property_wordpres_query',
            $wordpressQuery,
            $this
        );
    }
}
