<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Constracts\Query;
use WordLand\Template;

class Properties extends Renderer
{
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
