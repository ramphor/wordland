<?php
namespace WordLand\Builder;

use WordLand\Abstracts\DataBuilder;
use WordLand\Property;

class PropertyBuilder extends DataBuilder
{
    protected $post;
    protected $property;

    public function __construct(&$post)
    {
        $this->post     = $post;
        $this->property = new Property();
    }

    public function build()
    {
        $this->parseBaseData();
    }

    /**
     * Parse data from original WordPress post to WordLand property
     *
     * @return void
     */
    public function parseBaseData()
    {
        // Only support WordPress post
        if (!is_a($this->post, \WP_Post::class)) {
            return;
        }
        $this->property->setName($this->post->post_title);
    }

    public function getProperty()
    {
        return $this->property;
    }
}
