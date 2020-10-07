<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;
use WordLand\Template;
use WordLand\Query\PropertyQuery;

class PropertyListingCategoryTabs extends Renderer
{
    protected $categoryTerms = array();

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
        $query = new PropertyQuery(array(
            'term' => $this->categoryTerms[0],
            'limit' => $this->props['limit'],
        ));

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
            'category/property-listing',
            array(
                'header' => $this->renderHeader(),
                'tabs' => $this->generateTabs(),
                'wp_query' => $this->query(),
                't' => Template::class,
            ),
            'wordland_category_property_listing',
            false
        );
    }
}
