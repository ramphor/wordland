<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Template;
use WordLand\Constracts\Query;

class PropertyListingCategoryTabs extends Renderer
{
    protected $wordlandQuery;
    protected $title;

    public function setQuery($wordlandQuery)
    {
        if (!is_a($wordlandQuery, Query::class)) {
            return;
        }
        $this->wordlandQuery = $wordlandQuery;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function get_content()
    {
        return Template::render(
            'category/property-listing',
            array(
            ),
            'wordland_category_property_listing',
            false
        );
    }
}
