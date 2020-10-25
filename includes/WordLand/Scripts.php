<?php
namespace WordLand;

use WordLand;

class Scripts
{
    const HANDLER_NAME = 'wordland';

    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 40);
        add_action('wp_enqueue_scripts', array($this, 'registerStyles'), 40);
    }

    protected function asset_url($path = '')
    {
        return sprintf(
            '%sassets/%s',
            plugin_dir_url(WORDLAND_PLUGIN_FILE),
            $path
        );
    }

    public function registerScripts()
    {
        $deps = array();
        if (!get_theme_support('render_js_template')) {
            wp_register_script('blueimp-tmpl', $this->asset_url('vendor/JavaScript-Templates/tmpl.js'), array(), '3.19.0', false);
            array_push($deps, 'blueimp-tmpl');
        }
        wp_register_script(static::HANDLER_NAME, $this->asset_url('js/wordland.js'), $deps, WordLand::$version);
        wp_enqueue_script(static::HANDLER_NAME);
    }

    public function registerStyles()
    {
        global $wp_styles;

        $deps = array();
        if (isset($wp_styles->registered['hover'])) {
            array_push($deps, 'hover');
        }
        wp_register_style(static::HANDLER_NAME, $this->asset_url('css/wordland.css'), $deps, WordLand::$version);
        wp_enqueue_style(static::HANDLER_NAME);
    }
}
