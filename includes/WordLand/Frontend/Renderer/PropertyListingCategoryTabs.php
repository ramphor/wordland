<?php
namespace WordLand\Frontend\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Template;
use WordLand\Query\PropertyQuery;

class PropertyListingCategoryTabs extends Renderer
{
    const RENDERDER_NAME = 'property_with_category_tabs';

    protected $categoryTerms = array();

    public function get_name()
    {
        return static::RENDERER_NAME;
    }

    public function renderHeader()
    {
        if ($this->props['title']) {
            return Template::render('common/header_text', array(
                'text' => $this->props['title'],
            ), null, false);
        }
        return '';
    }

    public function getCategoryTerms()
    {
        $categories = array();
        foreach ($this->props['category'] as $categoryID) {
            $term = get_term($categoryID);
            if (is_null($term)) {
                continue;
            }
            $this->categoryTerms[] = $term;
        }
    }

    public function query()
    {
        $args = array(
            'limit' => $this->props['limit'],
        );
        if (!empty($this->categoryTerms)) {
            $args['term'] = $this->categoryTerms[0];
        }
        $query = new PropertyQuery($args);

        return $query->getWordPressQuery();
    }

    public function generateTabs()
    {
        $tabs = array();
        foreach ($this->categoryTerms as $index => $term) {
            $tabs[$index] = array(
                'url' => get_term_link($term),
                'text' => $term->name,
            );
            if ($index === 0) {
                $tabs[$index]['class'] = 'active';
            }
        }
        return Template::render('common/tabs', compact('tabs'), null, false);
    }

    public function get_content()
    {
        // Don't show anything when categories list is empty
        if (!$this->props['category']) {
            return '';
        }
        // Get category for render
        $this->getCategoryTerms();

        return Template::render(
            'widget/property-listing-category-tabs',
            array(
                'header' => $this->renderHeader(),
                'tabs' => $this->generateTabs(),
                'wp_query' => $this->query(),
                't' => Template::class,
                'style' => array_get($this->props, 'layout_style', 'card')
            ),
            'wordland_category_property_listing',
            false
        );
    }
}
