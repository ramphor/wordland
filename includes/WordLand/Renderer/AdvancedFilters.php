<?php
namespace WordLand\Renderer;

use WordLand\Abstracts\Renderer;

class AdvancedFilters extends Renderer
{
    const RENDERER_NAME = 'advanced_filters';

    protected $props = array(
        'map_only' => false,
    );

    public function get_name()
    {
        return static::RENDERER_NAME;
    }

    public function get_content()
    {
        $pre = apply_filters_ref_array(
            'wordland_pre_render_advanced_filters',
            array(
                null,
                &$this
            )
        );

        if (!is_null($pre)) {
            return $pre;
        }

        echo 'advanced search';
    }
}
