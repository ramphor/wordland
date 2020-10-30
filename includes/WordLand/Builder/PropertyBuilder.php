<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand\Abstracts\PropertyBuilderAbstract;

class PropertyBuilder extends PropertyBuilderAbstract
{
    protected $property;
    protected $originalPost;

    public function buildContent()
    {
        $this->property->content = apply_filters(
            'the_content',
            $this->originalPost->post_content
        );
    }

    public function loadImages()
    {
    }

    public function getProperty()
    {
        if (!$this->property->ID) {
            return;
        }
        return apply_filters_ref_array('wordland_builder_get_property', array(&$this->property, $this));
    }
}
