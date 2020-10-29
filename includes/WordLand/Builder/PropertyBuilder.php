<?php
namespace WordLand\Builder;

use WP_Post;
use WordLand\Abstracts\DataBuilder;
use WordLand\Property;

class PropertyBuilder extends DataBuilder
{
    protected $property;
    protected $originalPost;
    protected $isBuildContent;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->property = new Property();
    }

    public function setPost($post)
    {
        if (!is_a($post, WP_Post::class)) {
            return;
        }
        $this->originalPost = $post;
    }

    public function buildBaseData()
    {
        if (is_null($this->originalPost)) {
            return;
        }
        $this->property->ID = $this->originalPost->ID;
        $this->property->name = apply_filters(
            'the_title',
            $this->originalPost->post_title
        );
        $this->property->description = get_the_excerpt($this->originalPost);
        if ($this->isBuildContent) {
            $this->property->content = apply_filters(
                'the_content',
                $this->originalPost->post_content
            );
        }
    }

    public function build()
    {
        $this->buildBaseData();
    }

    public function getProperty()
    {
        if (!$this->property->ID) {
            return;
        }
        return $this->property;
    }
}
