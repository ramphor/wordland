<?php
namespace WordLand\Frontend\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\PostTypes;
use WordLand\Template;
use Jankx\TemplateEngine\Data\Term;

class PropertyCategories extends Renderer
{
    protected $props = array(
        'hide_empty' => false,
    );

    public function get_name()
    {
        return 'property_categories';
    }

    public function get_content()
    {
        $terms = get_terms(array(
            'taxonomy' => PostTypes::PROPERTY_CATEGORY_TAX,
            'hide_empty' => array_get($this->props, 'hide_empty'),
        ));
        $terms = array_map(function ($term) {
            return new Term($term);
        }, $terms);

        return Template::render(
            'widget/property-categories',
            array(
                'categories' => $terms,
            )
        );
    }
}
