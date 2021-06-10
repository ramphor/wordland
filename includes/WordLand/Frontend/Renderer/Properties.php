<?php
namespace WordLand\Frontend\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Constracts\Query;
use WordLand\Template;

class Properties extends Renderer
{
    const RENDERER_NAME = 'properties';

    public function get_name()
    {
        return static::RENDERER_NAME;
    }

    public function get_content()
    {
        $wp_query = $this->query->getWordPressQuery();
        Template::render('widget/properties', array(
            'header' => $this->getHeaderContent(),
            'wp_query' => $wp_query,
            'style' => array_get($this->props, 'layout_style', 'card'),
            't' => Template::class,
        ));
    }
}
