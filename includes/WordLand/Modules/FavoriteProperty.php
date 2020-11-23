<?php
namespace WordLand\Modules;

use WordLand\Abstracts\ModuleAbstract;

class FavoriteProperty extends ModuleAbstract
{
    const MODULE_NAME = 'favorite_property';

    public function get_name()
    {
        return static::MODULE_NAME;
    }

    public function init()
    {
        register_post_collection($this->get_name(), array(
            'name' => __('Favorite Properties', 'wordland'),
            'public' => true,
        ));
        $this->setup_ajax();
    }

    public function setup_ajax()
    {
        add_action('wp_ajax_wordland_favorite_property', array($this, 'favoriteProperty'));
    }

    public function favoriteProperty()
    {
    }
}
