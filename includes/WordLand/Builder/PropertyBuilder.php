<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand\Abstracts\PropertyBuilderAbstract;
use WordLand\PostTypes;

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

    public function buildTypes()
    {
    }

    public function buildCategories()
    {
    }

    public function buildTags()
    {
    }

    public function buildLocations()
    {
    }

    public function getPrimaryAgent()
    {
    }

    public function getPropertyVisibilities()
    {
        $terms = wp_get_post_terms($this->property->ID, PostTypes::PROPERTY_VISIBILITY, array(
            'fields' => 'id=>name'
        ));

        $this->property->visibilities = apply_filters(
            'wordland_visibilities',
            $terms,
            $this->property
        );
    }
}
