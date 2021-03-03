<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand\Abstracts\PropertyBuilderAbstract;
use WordLand\PostTypes;
use WordLand\Agent;

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

    public function getCoordinate()
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
        $owner = get_userdata($this->originalPost->post_author);

        if ($owner) {
            unset($owner->user_pass);
            // Create primary agent
            $agent = new Agent($owner->display_name);
        } else {
            $agent = new Agent(__('Guest'));
        }

        $this->property->primary_agent = apply_filters(
            'wordland_primary_agent',
            $agent,
            $owner,
            $this->property
        );
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
