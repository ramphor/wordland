<?php
namespace WordLand\Abstracts;

use WordLand\Constracts\PropertyBuilder;
use WordLand\Property;

abstract class PropertyBuilderAbstract implements PropertyBuilder
{
    protected $property;
    protected $originalPost;

    public function __construct($post = null)
    {
        $this->reset();
        if ($post) {
            $this->setPost($post);
        }
    }

    public function reset()
    {
        $this->property = new Property();
    }

    public function setPost($post)
    {

        if (!is_a($post, \WP_Post::class)) {
            return;
        }

        $this->originalPost = $post;
    }


    public function buildBaseData()
    {
        if (is_null($this->originalPost)) {
            return;
        }
        $this->property->ID = (int)$this->originalPost->ID;
        $this->property->name = apply_filters(
            'the_title',
            $this->originalPost->post_title
        );
        $this->property->description = get_the_excerpt($this->originalPost);
    }

    // The alias of buildBaseData
    public function build()
    {
        $this->buildBaseData();
    }

    public function getProperty()
    {
        if (!$this->property->ID) {
            return;
        }

        return apply_filters(
            'wordland_builder_get_property',
            $this->property,
            $this
        );
    }
}
