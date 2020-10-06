<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;

class AdvancedFilters extends Renderer
{
    public function get_content() {
        $pre = apply_filters('wordland_pre_render_advanced_filters', null);
        if (!is_null($pre)) {
            return $pre;
        }

        echo 'advanced search';
    }
}
