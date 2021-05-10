<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;
use WordLand\Renderer\AdvancedFilters;

class AdvancedSearch extends ModuleAbstract
{
    const MODULE_NAME = 'advanced_search';
    const SHORTCODE_NAME = 'wordland_advanced_search';

    protected $advanced_search = false;

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        add_action('wp', array($this, 'detect_advanced_search'));

        add_shortcode(static::SHORTCODE_NAME, array($this, 'register_shortcode'));
    }

    public function register_shortcode()
    {
        $advancedFilters = new AdvancedFilters();

        return $advancedFilters;
    }

    public function detect_advanced_search($a)
    {
        $queried_object = get_queried_object();
        if (!isset($queried_object->post_type) || $queried_object->post_type !== 'page') {
            return;
        }
        $this->advanced_search = has_shortcode($queried_object->post_content, static::SHORTCODE_NAME);
    }

    public function is_advanced_search()
    {
        return $this->advanced_search;
    }
}
