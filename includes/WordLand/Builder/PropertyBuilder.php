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

    public function getGeoLocation()
    {
    }
}
